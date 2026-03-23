<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class AccountLinkService extends AbstractService
{
    /**
     * Creates an AccountLink object that includes a single-use FooEventsPOS\Stripe URL that the
     * platform can redirect their user to in order to take them through the Connect
     * Onboarding flow.
     *
     * @param null|array{account: string, collect?: string, collection_options?: array{fields?: string, future_requirements?: string}, expand?: string[], refresh_url?: string, return_url?: string, type: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\AccountLink
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/account_links', $params, $opts);
    }
}
