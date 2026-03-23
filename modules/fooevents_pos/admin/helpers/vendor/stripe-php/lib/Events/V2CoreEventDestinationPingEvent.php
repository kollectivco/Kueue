<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Events;

/**
 * @property \FooEventsPOS\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 */
class V2CoreEventDestinationPingEvent extends \FooEventsPOS\Stripe\V2\Event
{
    const LOOKUP_TYPE = 'v2.core.event_destination.ping';

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @return \FooEventsPOS\Stripe\V2\EventDestination
     *
     * @throws \FooEventsPOS\Stripe\Exception\ApiErrorException if the request fails
     */
    public function fetchRelatedObject()
    {
        $apiMode = \FooEventsPOS\Stripe\Util\Util::getApiMode($this->related_object->url);
        list($object, $options) = $this->_request(
            'get',
            $this->related_object->url,
            [],
            ['stripe_account' => $this->context],
            [],
            $apiMode
        );

        return \FooEventsPOS\Stripe\Util\Util::convertToStripeObject($object, $options, $apiMode);
    }
}
