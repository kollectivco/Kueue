<?php

declare(strict_types=1);

namespace Camoo\Sms;

use Camoo\Sms\Lib\Utils;

use const PHP_VERSION_ID;

/**
 * Class Constants
 */
class Constants
{
    public const CLIENT_VERSION = '3.3.4';

    public const CLIENT_TIMEOUT = 30; // 30 sec

    public const MIN_PHP_VERSION = 80100;

    public const DS = '/';

    public const END_POINT_URL = 'https://api.camoo.cm';

    public const END_POINT_VERSION = 'v1';

    public const RESOURCE_VIEW = 'view';

    public const RESOURCE_BALANCE = 'balance';

    public const RESOURCE_TOP_UP = 'topup';

    public const JSON_RESPONSE_FORMAT = 'json';

    public const SMS_MAX_RECIPIENTS = 50;

    public const CLEAR_OBJECT = [Base::class, 'clear'];

    public const MAP_MOBILE = [Utils::class, 'mapMobile'];

    public const PERSONALIZE_MSG_KEYS = ['%NAME%'];

    public const CREDENTIAL_ELEMENTS = ['api_key', 'api_secret'];

    public static function getPhpVersion(): string
    {
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION); //@codeCoverageIgnore
            define('PHP_VERSION_ID', $version[0] * 10000 + $version[1] * 100 + $version[2]); //@codeCoverageIgnore
        }

        if (PHP_VERSION_ID < self::MIN_PHP_VERSION) {
            trigger_error(
                'Your PHP-Version belongs to a release that is no longer supported.' .
                'You should upgrade your PHP version as soon as possible,' .
                ' as it may be exposed to un-patched security vulnerabilities',
                E_USER_ERROR
            );//@codeCoverageIgnore
        }

        return 'PHP/' . PHP_VERSION_ID;
    }

    public static function getSMSPath(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR;
    }
}
