<?php

declare(strict_types=1);

namespace Camoo\Sms\Lib;

use stdClass;

final class NormalizeMessageResponse
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function get(bool $associative = true): array|stdClass|null
    {
        $value = Utils::decodeJson($this->content, true);
        if ($value === null) {
            return null;
        }

        $sms = $value['sms'] ?? null;
        unset($value['sms']);
        $messages = $sms['messages'] ?? null;
        unset($sms['messages']);

        $value = Utils::normaliseKeys($value, $associative);
        if ($sms = Utils::normaliseKeys($sms, $associative)) {
            !$associative ? $value->sms = $sms : $value['sms'] = $sms;
            if ($messages = Utils::normaliseKeys($messages, $associative)) {
                !$associative ? $value->sms->messages = $messages : $value['sms']['messages'] = $messages;
            }
        }

        return $value;
    }
}
