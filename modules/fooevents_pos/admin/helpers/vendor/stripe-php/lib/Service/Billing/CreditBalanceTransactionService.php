<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class CreditBalanceTransactionService extends \FooEventsPOS\Stripe\Service\AbstractService
{
    /**
     * Retrieve a list of credit balance transactions.
     *
     * @param null|array{credit_grant?: string, customer: string, ending_before?: string, expand?: string[], limit?: int, starting_after?: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Collection<\FooEventsPOS\Stripe\Billing\CreditBalanceTransaction>
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/billing/credit_balance_transactions', $params, $opts);
    }

    /**
     * Retrieves a credit balance transaction.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Billing\CreditBalanceTransaction
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/billing/credit_balance_transactions/%s', $id), $params, $opts);
    }
}
