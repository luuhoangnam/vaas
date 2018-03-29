<?php

namespace App\Http\Controllers\API;

use App\eBay\TradingAPI;
use App\Exceptions\Amazon\ProductAdvertisingAPIException;
use App\Exceptions\Amazon\SomethingWentWrongException;
use App\Exceptions\TradingApiException;
use App\Http\Controllers\Controller;
use App\Item;
use App\Jobs\Amazon\ExtractOffers;
use App\Sourcing\AmazonAPI;
use App\Sourcing\AmazonCrawler;
use App\Sourcing\AmazonIdMode;
use Carbon\Carbon as PureCarbon;
use DTS\eBaySDK\Trading\Enums\AckCodeType;
use DTS\eBaySDK\Trading\Enums\DetailLevelCodeType;
use DTS\eBaySDK\Trading\Types\GetItemRequestType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsRequestType;
use DTS\eBaySDK\Trading\Types\GetItemTransactionsResponseType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use DTS\eBaySDK\Trading\Types\TransactionType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ResearchItemController extends Controller
{
    public function show($id)
    {
        $request = new GetItemRequestType;

        $request->IncludeWatchCount    = true;
        $request->IncludeItemSpecifics = true;

        $request->ItemID = (string)$id;

        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        $request->OutputSelector = [
            'ItemID',
            'Country',
            'ConditionDisplayName',
            'DispatchTimeMax',
            'ItemSpecifics',
            'ListingDetails',
            'ListingType',
            'Quantity',
            'PostalCode',
            'PrimaryCategory',
            'PictureDetails',
            'ProductListingDetails',
            'Title',
            'SellingStatus',
            'SKU',
            'Site',
            'TopRatedListing',
            'Variations',
        ];

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->trading()->getItem($request, 60); // Cached for 1 Day

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        $item        = $this->extract($response->Item);
        $performance = $this->performance($id);
        $source      = $this->guessSource($response->Item);
        $listed_on   = @$source['asin'] ? $this->listedOn($source['asin']) : null;

        # CALCULATE PROFIT, MARGIN, BEST OFFER
        $offers = $source['offers'];
        /** @noinspection PhpUndefinedMethodInspection */
        $best_offer = $offers->first();

        if ($best_offer) {
            $profit = round($this->calcProfit($item['price'], $best_offer), 2, PHP_ROUND_HALF_EVEN);
            $margin = round($profit / $item['price'], 4, PHP_ROUND_HALF_EVEN);
        } else {
            $profit = $margin = null;
        }

        $warnings = [];

        if ($margin > .2) {
            $warnings[] = 'Margin is not normal (too high)';
        }

        if ($profit > 10) {
            $warnings[] = 'Profit is not normal (too high)';
        }

        if ($item['status'] === 'Completed') {
            $warnings[] = 'Competitor\'s item has ended';
        }

        if ($best_offer) {
            $perf30D         = collect($performance)->where('period', '=', 30)->first();
            $priceIsNotRight = $perf30D['count'] && $perf30D['average_price'] / $best_offer['price'] >= 2;

            if ($priceIsNotRight) {
                $warnings[] = 'Price is seems not right';
            }
        }

        $more = compact('performance', 'source', 'profit', 'margin', 'listed_on', 'warnings', 'best_offer');

        return array_merge($item, $more);
    }

    protected function calcProfit($sellingPrice, $offer)
    {
        $fees = $sellingPrice * (9.15 + 3.9) / 100 + 0.3;

        return $sellingPrice - $this->costIncTax($offer) - $fees;
    }

    protected function costIncTax($offer)
    {
        return $offer['price'] * ($offer['has_tax'] ? 1.09 : 1);
    }

    public function extract(ItemType $item): array
    {
        return [
            'item_id'              => $item->ItemID,
            'title'                => $item->Title,
            'site'                 => $item->Site,
            'condition'            => $item->ConditionDisplayName,
            'primary_category'     => [
                'id'      => (int)$item->PrimaryCategory->CategoryID,
                'name'    => $item->PrimaryCategory->CategoryName,
                'parents' => array_reverse(explode(':', $item->PrimaryCategory->CategoryName)),
            ],
            'picture'              => $item->PictureDetails->GalleryURL,
            'country'              => $item->Country,
            'price'                => $item->SellingStatus->CurrentPrice->value,
            'currency'             => $item->SellingStatus->CurrentPrice->currencyID,
            'status'               => $item->SellingStatus->ListingStatus,
            'quantity'             => $item->Quantity,
            'quantity_sold'        => $item->SellingStatus->QuantitySold,
            'sku'                  => $item->SKU,
            'postal_code'          => $item->PostalCode,
            'handling_time'        => $item->DispatchTimeMax,
            'start_time'           => app_carbon($item->ListingDetails->StartTime)->toDateTimeString(),
            'end_time'             => app_carbon($item->ListingDetails->EndTime)->toDateTimeString(),
            'listing_type'         => $item->ListingType,
            'has_variants'         => (bool)$item->Variations,
            'is_top_rated_listing' => $item->TopRatedListing,
            'attributes'           => $this->normalizeAttribute($item),
            // Fees
            'final_value_fee'      => $item->SellingStatus->CurrentPrice->value * 0.0915,
            'paypal_fee'           => $item->SellingStatus->CurrentPrice->value * 0.039 + 0.3,
        ];
    }

    protected function performance($itemID): array
    {
        $request = new GetItemTransactionsRequestType;

        $request->ItemID       = (string)$itemID;
        $request->Pagination   = new PaginationType(['EntriesPerPage' => 100, 'PageNumber' => 1]);
        $request->NumberOfDays = 30;

        $request->DetailLevel = [DetailLevelCodeType::C_RETURN_ALL];

        $transactions = new Collection;

        do {
            /** @noinspection PhpUndefinedMethodInspection */
            /** @var GetItemTransactionsResponseType $response */
            $response = $this->trading()->getItemTransactions($request, 60 * 24); // Cached for 1 Day

            if ($response->TransactionArray->Transaction) {
                foreach ($response->TransactionArray->Transaction as $transaction) {
                    $transactions->push($transaction);
                }
            }

            $request->Pagination->PageNumber++;
        } while ($response->HasMoreTransactions);

        // Transaction Last 30 Days
        $performance = [7, 14, 21, 30];

        foreach ($performance as $key => $period) {
            $performance[$key] = $this->transactionDetailsForPeriod(
                $transactions,
                Carbon::now()->subDays($period),
                Carbon::now()
            );
        }

        return $performance;
    }

    protected function transactionDetailsForPeriod(Collection $transactions, PureCarbon $since, PureCarbon $until)
    {
        $filtered = $transactions->filter(function (TransactionType $transaction) use ($since, $until) {
            return app_carbon($transaction->CreatedDate)->between($since, $until);
        })->map(function (TransactionType $transaction) {
            return $transaction->toArray();
        });

        $period   = $until->diffInDays($since);
        $count    = $filtered->count();
        $revenue  = round($filtered->sum('TransactionPrice.value'), 2, PHP_ROUND_HALF_EVEN);
        $quantity = round($filtered->sum('QuantityPurchased'), 0, PHP_ROUND_HALF_EVEN);

        return array_merge(
            compact('period', 'count', 'revenue', 'quantity'),
            [
                'average_price'    => $count ? round($revenue / $count, 2, PHP_ROUND_HALF_EVEN) : 0,
                'average_quantity' => $count ? round($quantity / $count, 2, PHP_ROUND_HALF_EVEN) : 0,
            ]
        );
    }

    protected function normalizeAttribute(ItemType $item)
    {
        if ( ! $item->ItemSpecifics && ! $item->ProductListingDetails) {
            return [];
        }

        $attributes = [];

        // ItemSpecifics
        if (@$item->ItemSpecifics->NameValueList) {
            foreach ($item->ItemSpecifics->NameValueList as $specific) {
                $attributes[$specific->Name] = array_first($specific->Value);
            }
        }

        // ProductListingDetails
        @$item->ProductListingDetails->UPC && $attributes['UPC'] = $item->ProductListingDetails->UPC;
        @$item->ProductListingDetails->EAN && $attributes['EAN'] = $item->ProductListingDetails->EAN;
        @$item->ProductListingDetails->ISBN && $attributes['ISBN'] = $item->ProductListingDetails->ISBN;

        if (@$item->ProductListingDetails->NameValueList) {
            foreach ($item->ProductListingDetails->NameValueList as $detail) {
                $attributes[$detail->Name] = array_first($detail->Value);
            }
        }

        if (@$item->ProductListingDetails->BrandMPN) {
            $attributes['Brand'] = @$item->ProductListingDetails->BrandMPN->Brand;
            $attributes['MPN']   = @$item->ProductListingDetails->BrandMPN->MPN;
            $attributes['MPN']   = @$item->ProductListingDetails->BrandMPN->MPN;
        }

        return $attributes;
    }

    protected function trading()
    {
        return TradingAPI::random();
    }

    protected function guessSource(ItemType $item)
    {
        $cacheKey  = md5("items:{$item->ItemID}:source");
        $cacheTime = 60 * 24; // Cache 1 Day for Source

        return cache()->remember($cacheKey, $cacheTime, function () use ($item) {
            try {
                if ($asin = $this->itemASIN($item)) {
                    $product = AmazonAPI::inspect($asin, false);
                } elseif ($upc = $this->itemUPC($item)) {
                    $product = AmazonAPI::inspect($upc, false, AmazonIdMode::UPC);
                } elseif ($ean = $this->itemEAN($item)) {
                    $product = AmazonAPI::inspect($ean, false, AmazonIdMode::EAN);
                } elseif ($ibsn = $this->itemISBN($item)) {
                    $product = AmazonAPI::inspect($ibsn, false, AmazonIdMode::ISBN);
                } else {
                    return null;
                }

                try {
                    $offers = ExtractOffers::dispatchNow($product['asin']);
                } catch (SomethingWentWrongException $exception) {
                    $offers = null;
                }

                $offers = collect($offers)->sortBy(function ($offer) {
                    return $this->costIncTax($offer);
                });

                return array_merge($product, compact('offers'));
            } catch (ProductAdvertisingAPIException $exception) {
                if ($asin = $this->itemASIN($item) && $exception->getCode() === 'AWS.ECommerceService.ItemNotAccessible') {
                    return AmazonCrawler::get($asin);
                }

                if ($exception->getCode() === 'AWS.InvalidParameterValue') {
                    return null;
                }
            }

            return null;
        });
    }

    protected function itemASIN(ItemType $item)
    {
        if ($item->SKU && preg_match('/^[\d\w]{10}$/i', $item->SKU)) {
            return $item->SKU;
        }

        return null;
    }

    protected function itemUPC(ItemType $item)
    {
        if ($upc = $this->itemAttribute($item, 'UPC')) {
            return $upc;
        }

        // ProductListingDetails
        if ($upc = @$item->ProductListingDetails->UPC) {
            return $upc;
        }

        return null;
    }

    protected function itemEAN(ItemType $item)
    {
        if ($ean = $this->itemAttribute($item, 'EAN')) {
            return $ean;
        }

        if ($ean = @$item->ProductListingDetails->EAN) {
            return $ean;
        }

        return null;
    }

    protected function itemISBN(ItemType $item)
    {
        if ($isbn = $this->itemAttribute($item, 'ISBN')) {
            return $isbn;
        }

        if ($isbn = @$item->ProductListingDetails->ISBN) {
            return $isbn;
        }

        return null;
    }

    protected function itemAttribute(ItemType $item, $name)
    {
        if ( ! @$item->ItemSpecifics) {
            return null;
        }

        foreach ($item->ItemSpecifics->NameValueList as $attr) {
            if ($attr->Name === $name) {
                return array_first($attr->Value);
            }
        }

        return null;
    }

    protected function listedOn($sku)
    {
        return Item::query()
                   ->with('account')
                   ->where('sku', $sku)
                   ->get()
                   ->pluck('account.username')
                   ->values();
    }

    protected function averagePrice(Collection $transactions)
    {
        return $transactions->average(function (TransactionType $transaction) {
            return $transaction->TransactionPrice->value;
        });
    }

    protected function averageQuantity(Collection $transactions)
    {
        return $transactions->average(function (TransactionType $transaction) {
            return $transaction->QuantityPurchased;
        });
    }

    protected function transactionChart(Collection $transactions)
    {
        $minDate = $transactions->min(function (TransactionType $transactionType) {
            return $transactionType->CreatedDate;
        });

        $maxDate = $transactions->max(function (TransactionType $transactionType) {
            return $transactionType->CreatedDate;
        });

        $minDate = app_carbon($minDate);
        $maxDate = app_carbon($maxDate);

        $dates = date_range($minDate, $maxDate);

        $data = $dates->map(function (PureCarbon $date) use ($transactions) {
            $filtered = $transactions->filter(function (TransactionType $transaction) use ($date) {
                return $date->isSameDay(
                    app_carbon($transaction->CreatedDate)
                );
            })->map(function (TransactionType $transaction) {
                return $transaction->TransactionPrice->value;
            });

            return [
                'date'    => $date->toDateString(),
                'count'   => $filtered->count(),
                'revenue' => round($filtered->sum(), 2),
            ];
        });

        $orders   = $data->pluck('count');
        $revenues = $data->pluck('revenue');

        $orderAxisStep = round($orders->max()) ?: 1;
        $maxOrdersAxis = (round($orders->max() / $orderAxisStep, 0, PHP_ROUND_HALF_UP) + 1) * $orderAxisStep;

        $revenueAxisStep = round($revenues->max()) ?: 1;
        $maxRevenueAxis  = (round($revenues->max() / $revenueAxisStep, 0, PHP_ROUND_HALF_UP) + 1) * $revenueAxisStep;

        return [
            'type' => 'bar',

            'data' => [
                'labels'   => $dates->toDateStringCollection()->toArray(),
                'datasets' => [
                    [
                        'type'            => 'bar',
                        'label'           => 'Orders',
                        'backgroundColor' => "rgba(54, 162, 235, 0.2)",
                        'borderColor'     => "rgb(54, 162, 235)",
                        'data'            => $orders->toArray(),
                        'yAxisID'         => 'orders',
                    ],
                    [
                        'type'            => 'line',
                        'label'           => 'Revenue',
                        'fill'            => false,
                        'backgroundColor' => "rgba(255, 205, 86, 0.2)",
                        'borderColor'     => "rgb(255, 205, 86)",
                        'data'            => $revenues->toArray(),
                        'yAxisID'         => 'revenue',
                    ],
                ],
            ],

            'options' => [
                'tooltips' => [
                    'titleFontFamily' => 'Fira Code',
                ],
                'scales'   => [
                    'xAxes' => [
                        [
                            'gridLines' => [
                                'display' => false,
                            ],
                        ],
                    ],
                    'yAxes' => [
                        [
                            'id'        => 'orders',
                            'position'  => 'left',
                            'ticks'     => [
                                'beginAtZero' => true,
                                'max'         => $maxOrdersAxis,
                                'stepSize'    => $orderAxisStep,
                            ],
                            'gridLines' => [
                                'display' => false,
                            ],
                        ],
                        [
                            'id'        => 'revenue',
                            'position'  => 'right',
                            'ticks'     => [
                                'beginAtZero' => true,
                                'max'         => $maxRevenueAxis,
                                'stepSize'    => $revenueAxisStep,
                            ],
                            'gridLines' => [
                                'display' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];

    }
}
