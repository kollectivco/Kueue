<?php

namespace FooEventsPOS\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \FooEventsPOS\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }

        return \FooEventsPOS\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
