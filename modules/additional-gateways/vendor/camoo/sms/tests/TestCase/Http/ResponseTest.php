<?php

namespace CamooSms\Test\TestCase\Http;

use Camoo\Sms\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testCanGetXml(): void
    {
        $string = '<ul><li>first</li><li>second</li></ul>';
        $response = new Response($string, 200, 'xml');
        $this->assertStringStartsWith('<?xml version="1.0"?>', $response->getXml());
    }

    public function testCanNotGetXmlOnJson(): void
    {
        $string = '<ul><li>first</li><li>second</li></ul>';
        $response = new Response($string);
        $this->assertNull($response->getXml());
    }

    public function testCanNotGetXmlOnWrongXml(): void
    {
        $string = 'fooBar';
        $response = new Response($string, 200, 'xml');
        $this->assertNull($response->getXml());
    }
}
