<?php

declare(strict_types=1);

namespace Camoo\Sms;

interface ObjectHandlerInterface
{
    public static function create(): object;
}
