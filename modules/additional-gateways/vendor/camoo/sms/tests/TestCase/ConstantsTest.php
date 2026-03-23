<?php

namespace CamooSms\Test\TestCase;

use Camoo\Sms\Constants;
use PHPUnit\Framework\TestCase;

/**
 * Class ConstantsTest
 *
 * @author CamooSarl
 *
 * @covers \Camoo\Sms\Constants
 */
class ConstantsTest extends TestCase
{
    public function testGetPhpVersion(): void
    {
        $this->assertStringContainsString('PHP/', Constants::getPhpVersion());
    }

    public function testGetSMSPath(): void
    {
        $this->assertSame(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR, Constants::getSMSPath());
    }
}
