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

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->trading()->getItem($request, 60); // Cached for 1 Day

        if ($response->Ack === AckCodeType::C_FAILURE) {
            throw new TradingApiException($request, $response);
        }

        $transactions = $this->performance($id);

        return array_merge($this->extract($response->Item), compact('transactions'));
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
            'source'               => $this->guessSource($item),
            'listed_on'            => $item->SKU ? $this->listedOn($item->SKU) : null,
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
        $now = Carbon::now();

        $sold7d  = $this->countTransactionForPeriod($transactions, (clone $now)->subDays(7), $now);
        $sold14d = $this->countTransactionForPeriod($transactions, (clone $now)->subDays(14), $now);
        $sold21d = $this->countTransactionForPeriod($transactions, (clone $now)->subDays(21), $now);
        $sold30d = $this->countTransactionForPeriod($transactions, (clone $now)->subDays(30), $now);

        return [
            'average_price'    => $this->averagePrice($transactions),
            'average_quantity' => $this->averageQuantity($transactions),
            'sold_7d'          => $sold7d,
            'sold_14d'         => $sold14d,
            'sold_21d'         => $sold21d,
            'sold_30d'         => $sold30d,
            'chart'            => $this->transactionChart($transactions),
        ];
    }

    protected function countTransactionForPeriod(Collection $transactions, PureCarbon $since, PureCarbon $until): int
    {
        $filtered = $transactions->filter(function (TransactionType $transaction) use ($since, $until) {
            return app_carbon($transaction->CreatedDate)->between($since, $until);
        });

        return $filtered->count();
    }

    protected function normalizeAttribute(ItemType $item)
    {
        if ( ! $item->ItemSpecifics) {
            return [];
        }

        $attributes = [];

        foreach ($item->ItemSpecifics->NameValueList as $specific) {
            $attributes[$specific->Name] = $specific->Value[0];
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
                } elseif ($ibsn = $this->itemIBSN($item)) {
                    $product = AmazonAPI::inspect($ibsn, false, AmazonIdMode::IBSN);
                } else {
                    return null;
                }

                try {
                    $offers = ExtractOffers::dispatchNow($product['asin']);
                } catch (SomethingWentWrongException $exception) {
                    $offers = null;
                }

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
        if ( ! $item->SKU) {
            return null;
        }

        if (strlen($item->SKU) !== 10) {
            return null;
        }

        return $item->SKU;
    }

    protected function itemUPC(ItemType $item)
    {
        return $this->itemAttribute($item, 'UPC');
    }

    protected function itemEAN(ItemType $item)
    {
        return $this->itemAttribute($item, 'EAN');
    }

    protected function itemIBSN(ItemType $item)
    {
        return $this->itemAttribute($item, 'IBSN');
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
