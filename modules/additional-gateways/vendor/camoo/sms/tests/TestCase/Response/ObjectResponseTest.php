<?php

declare(strict_types=1);

namespace CamooSms\Test\TestCase\Response;

use Camoo\Sms\Http\Response;
use Camoo\Sms\Response\ObjectResponse;
use PHPUnit\Framework\TestCase;

class ObjectResponseTest extends TestCase
{
    public function testCanCreateInstance(): void
    {
        $json = file_get_contents(dirname(__DIR__, 2) . '/Fixture/balance.json');
        $response = new Response($json);
        $response1 = new ObjectResponse($response);
        $this->assertInstanceOf(ObjectResponse::class, $response1);
    }
}
