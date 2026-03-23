<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class MeterEventService extends \FooEventsPOS\Stripe\Service\AbstractService
{
    /**
     * Creates a billing meter event.
     *
     * @param null|array{event_name: string, expand?: string[], identifier?: string, payload: array<string, string>, timestamp?: int} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Billing\MeterEvent
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/billing/meter_events', $params, $opts);
    }
}
