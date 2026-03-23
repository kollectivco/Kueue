<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service\Forwarding;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class RequestService extends \FooEventsPOS\Stripe\Service\AbstractService
{
    /**
     * Lists all ForwardingRequest objects.
     *
     * @param null|array{created?: array{gt?: int, gte?: int, lt?: int, lte?: int}, ending_before?: string, expand?: string[], limit?: int, starting_after?: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Collection<\FooEventsPOS\Stripe\Forwarding\Request>
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/forwarding/requests', $params, $opts);
    }

    /**
     * Creates a ForwardingRequest object.
     *
     * @param null|array{expand?: string[], metadata?: array<string, string>, payment_method: string, replacements: string[], request: array{body?: string, headers?: array{name: string, value: string}[]}, url: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Forwarding\Request
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/forwarding/requests', $params, $opts);
    }

    /**
     * Retrieves a ForwardingRequest object.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Forwarding\Request
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/forwarding/requests/%s', $id), $params, $opts);
    }
}
