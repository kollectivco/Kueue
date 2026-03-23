<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Service\TestHelpers;

/**
 * @phpstan-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 *
 * @psalm-import-type RequestOptionsArray from \FooEventsPOS\Stripe\Util\RequestOptions
 */
class TestClockService extends \FooEventsPOS\Stripe\Service\AbstractService
{
    /**
     * Starts advancing a test clock to a specified time in the future. Advancement is
     * done when status changes to <code>Ready</code>.
     *
     * @param string $id
     * @param null|array{expand?: string[], frozen_time: int} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\TestHelpers\TestClock
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function advance($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/test_clocks/%s/advance', $id), $params, $opts);
    }

    /**
     * Returns a list of your test clocks.
     *
     * @param null|array{ending_before?: string, expand?: string[], limit?: int, starting_after?: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\Collection<\FooEventsPOS\Stripe\TestHelpers\TestClock>
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/test_helpers/test_clocks', $params, $opts);
    }

    /**
     * Creates a new test clock that can be attached to new customers and quotes.
     *
     * @param null|array{expand?: string[], frozen_time: int, name?: string} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\TestHelpers\TestClock
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/test_helpers/test_clocks', $params, $opts);
    }

    /**
     * Deletes a test clock.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\TestHelpers\TestClock
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function delete($id, $params = null, $opts = null)
    {
        return $this->request('delete', $this->buildPath('/v1/test_helpers/test_clocks/%s', $id), $params, $opts);
    }

    /**
     * Retrieves a test clock.
     *
     * @param string $id
     * @param null|array{expand?: string[]} $params
     * @param null|RequestOptionsArray|\FooEventsPOS\Stripe\Util\RequestOptions $opts
     *
     * @return \FooEventsPOS\Stripe\TestHelpers\TestClock
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/test_helpers/test_clocks/%s', $id), $params, $opts);
    }
}
