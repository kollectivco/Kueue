<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class CreditBalanceSummaryService extends \FooEventsPOS\Stripe\Service\AbstractService
{
    /**
     * Retrieves the credit balance summary for a customer.
     *
     * @param null|array{customer: string, expand?: string[], filter: array{applicability_scope?: array{price_type?: string, prices?: array{id: string}[]}, credit_grant?: string, type: string}} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Billing\CreditBalanceSummary
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($params = null, $opts = null)
    {
        return $this->request('get', '/v1/billing/credit_balance_summary', $params, $opts);
    }
}
