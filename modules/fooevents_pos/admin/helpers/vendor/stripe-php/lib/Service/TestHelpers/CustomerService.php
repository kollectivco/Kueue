<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service\TestHelpers;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class CustomerService extends \FooEventsPOS\Stripe\Service\AbstractService
{
    /**
     * Create an incoming testmode bank transfer.
     *
     * @param string $id
     * @param null|array{amount: int, currency: string, expand?: string[], reference?: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\CustomerCashBalanceTransaction
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fundCashBalance($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/customers/%s/fund_cash_balance', $id), $params, $opts);
    }
}
