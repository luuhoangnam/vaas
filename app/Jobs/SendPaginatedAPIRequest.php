<?php

namespace App\Jobs;

use App\eBay\API;
use App\Support\APIResponseResolver;
use DTS\eBaySDK\Finding\Enums\AckValue;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedRequest;
use DTS\eBaySDK\Finding\Types\FindItemsAdvancedResponse;
use DTS\eBaySDK\Types\BaseType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ReflectionClass;

class SendPaginatedAPIRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData;
    protected $requestClass;
    protected $resolver;
    protected $sender;
    protected $method;

    public function __construct(API $sender, $method, BaseType $request, $resolver)
    {
        $this->sender = $sender;
        $this->method = $method;

        $this->requestData  = $request->toArray();
        $this->requestClass = get_class($request);

        $this->validateResolverClass($resolver);

        $this->resolver = $resolver;
    }

    public function handle()
    {
        /** @var FindItemsAdvancedRequest $request */
        $request = new $this->requestClass($this->requestData);

        do {
            $response = $this->sender->{$this->method}($request);

            if ($response->ack === AckValue::C_FAILURE) {
                $exception = forward_static_call([$this->resolver, 'exception']);

                throw new $exception($request, $response);
            }

            $request->paginationInput->pageNumber += 1;
        } while (forward_static_call([$this->resolver, 'hasMorePage'], $response));
    }

    protected function validateResolverClass($resolverClass)
    {
        if ( ! class_exists($resolverClass)) {
            throw new \InvalidArgumentException('Resolver Class must be exists');
        }
    }
}
