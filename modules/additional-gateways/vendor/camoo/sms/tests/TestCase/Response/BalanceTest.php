<?php

declare(strict_types=1);

namespace CamooSms\Test\TestCase\Response;

use Camoo\Sms\Entity\Money;
use Camoo\Sms\Http\Response;
use Camoo\Sms\Response\Balance;
use PHPUnit\Framework\TestCase;

class BalanceTest extends TestCase
{
    private ?Balance $response;

    protected function setUp(): void
    {
        parent::setUp();
        $json = file_get_contents(dirname(__DIR__, 2) . '/Fixture/balance.json');
        $response = new Response($json);
        $this->response = new Balance($response);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->response = null;
    }

    public function testCanCreateInstance(): void
    {
        $this->assertInstanceOf(Balance::class, $this->response);
    }

    public function testCanGetBalance(): void
    {
        $this->assertSame(3704.56, $this->response->getBalance());
    }

    public function testCanGetCurrency(): void
    {
        $this->assertEquals('XAF', $this->response->getCurrency());
    }

    public function testFailure(): void
    {
        $json = '{"message":"KO","balance":{},"code":201}';
        $response = new Response($json);
        $this->response = new Balance($response);
        $this->assertNull($this->response->getCurrency());
        $this->assertSame(0.00, $this->response->getBalance());
    }

    public function testCanGetMoney(): void
    {
        $money = $this->response->getMoney();
        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame($money->value, $this->response->getBalance());
        $this->assertEquals($money->currency, $this->response->getCurrency());
    }
}
