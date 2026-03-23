<?php

namespace CamooSms\Test\TestCase\Response;

use Camoo\Sms\Http\Response;
use Camoo\Sms\Response\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private ?Message $response;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->response = null;
    }

    public function testCanGetViewResponse(): void
    {
        $response = new Response(file_get_contents(dirname(__DIR__, 2) . '/Fixture/sms-view.json'));
        $this->response = new Message($response);
        $this->assertEquals('1661562859237661562194941475', $this->response->getId());
        $this->assertEquals('delivered', $this->response->getStatus());
        $this->assertEquals('+237612345678', $this->response->getTo());
        $this->assertEquals(17, $this->response->getMessagePrice());
        $this->assertEquals('Hello Kmer World! Déjà vu!', $this->response->getMessage());
        $this->assertEquals('2023-02-03T08:50:34+00:00', $this->response->getCreatedAt()->format(DATE_ATOM));
        $this->assertEquals('2023-02-03T10:00:40+00:00', $this->response->getStatusTime()->format(DATE_ATOM));
        $this->assertSame('CamooTest', $this->response->getSmsSender());
        $this->assertEmpty($this->response->getReference());
    }

    public function testCanGeSentResponse(): void
    {
        $response = new Response(file_get_contents(dirname(__DIR__, 2) . '/Fixture/sms-sent.json'));
        $this->response = new Message($response);
        $this->assertEquals('1981562859237661562194941475', $this->response->getId());
        $this->assertEquals('0', $this->response->getStatus());
        $this->assertEquals('+237612345678', $this->response->getTo());
        $this->assertEquals(20, $this->response->getMessagePrice());
        $this->assertEquals('Hello world', $this->response->getMessage());
        $this->assertNull($this->response->getCreatedAt());
        $this->assertNull($this->response->getStatusTime());
        $this->assertNull($this->response->getSmsSender());
        $this->assertEmpty($this->response->getReference());
    }
}
