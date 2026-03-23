<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service\V2\Core;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class EventService extends \FooEventsPOS\Stripe\Service\AbstractService
{
    /**
     * List events, going back up to 30 days.
     *
     * @param null|array{limit?: int, object_id: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\V2\Collection<\FooEventsPOS\Stripe\V2\Event>
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v2/core/events', $params, $opts);
    }

    /**
     * Retrieves the details of an event.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\V2\Event
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v2/core/events/%s', $id), $params, $opts);
    }
}
