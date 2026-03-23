<?php

// File generated from our OpenAPI spec

namespace FooEventsPOS\Stripe\Events;

/**
 * @property \FooEventsPOS\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 * @property \FooEventsPOS\Stripe\EventData\V1BillingMeterErrorReportTriggeredEventData $data data associated with the event
 */
class V1BillingMeterErrorReportTriggeredEvent extends \FooEventsPOS\Stripe\V2\Event
{
    const LOOKUP_TYPE = 'v1.billing.meter.error_report_triggered';

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @return \FooEventsPOS\Stripe\Billing\Meter
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

    public static function constructFrom($values, $opts = null, $apiMode = 'v2')
    {
        $evt = parent::constructFrom($values, $opts, $apiMode);
        if (null !== $evt->data) {
            $evt->data = \FooEventsPOS\Stripe\EventData\V1BillingMeterErrorReportTriggeredEventData::constructFrom($evt->data, $opts, $apiMode);
        }

        return $evt;
    }
}
