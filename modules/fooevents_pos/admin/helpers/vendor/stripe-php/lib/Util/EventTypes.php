<?php

namespace FooEventsPOS\Stripe\Util;

class EventTypes
{
    const thinEventMapping = [
        // The beginning of the section generated from our OpenAPI spec
        \FooEventsPOS\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::LOOKUP_TYPE => \FooEventsPOS\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::class,
        \FooEventsPOS\Stripe\Events\V1BillingMeterNoMeterFoundEvent::LOOKUP_TYPE => \FooEventsPOS\Stripe\Events\V1BillingMeterNoMeterFoundEvent::class,
        \FooEventsPOS\Stripe\Events\V2CoreEventDestinationPingEvent::LOOKUP_TYPE => \FooEventsPOS\Stripe\Events\V2CoreEventDestinationPingEvent::class,
        // The end of the section generated from our OpenAPI spec
    ];
}
