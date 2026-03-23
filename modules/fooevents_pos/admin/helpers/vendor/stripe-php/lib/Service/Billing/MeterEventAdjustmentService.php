<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class MeterEventAdjustmentService extends \FooEventsPOS\Stripe\Service\AbstractService
{
    /**
     * Creates a billing meter event adjustment.
     *
     * @param null|array{cancel?: array{identifier?: string}, event_name: string, expand?: string[], type: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Billing\MeterEventAdjustment
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/billing/meter_event_adjustments', $params, $opts);
    }
}
