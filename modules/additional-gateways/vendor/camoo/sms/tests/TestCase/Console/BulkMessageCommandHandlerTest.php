<?php

declare(strict_types=1);

namespace CamooSms\Test\TestCase\Console;

use Camoo\Http\Curl\Domain\Client\ClientInterface;
use Camoo\Http\Curl\Domain\Response\ResponseInterface;
use Camoo\Sms\Console\BulkMessageCommand;
use Camoo\Sms\Console\BulkMessageCommandHandler;
use Camoo\Sms\Entity\DbConfig;
use Camoo\Sms\Exception\BulkSendException;
use Camoo\Sms\Http\Command\ExecuteRequestCommandHandler;
use Camoo\Sms\Http\Response;
use Camoo\Sms\Interfaces\DriversInterface;
use Camoo\Sms\Message;
use PHPUnit\Framework\TestCase;

final class BulkMessageCommandHandlerTest extends TestCase
{
    private ?BulkMessageCommand $command;

    private ?ResponseInterface $response = null;

    private ?ClientInterface $client = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->response = $this->createMock(ResponseInterface::class);
        $this->client = $this->createMock(ClientInterface::class);
        $this->driver = $this->createMock(DriversInterface::class);
        $this->dbConfig = new DbConfig('dbName', 'dbUser', 'password', 'table');
        $this->command = new BulkMessageCommand(
            [
                'to' => [['name' => 'John Doe', 'mobile' => '+237612345678']],
                'message' => 'Hello world',
                'from' => 'UnitTest',
            ],
            $this->driver,
            $this->dbConfig
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->command = null;
        $this->response = null;
        $this->client = null;
    }

    public function testCanHandle(): void
    {
        $response = (object)[
            '_message' => 'succes',
            'sms' => (object)[
                'message-count' => 1,
                'messages' => [
                    0 => (object)[
                        'status' => 0,
                        'message-id' => '1661562859237661562194941475',
                        'message' => 'Hello world',
                        'to' => '+237612345678',
                        'remaining-balance' => '3857.56',
                        'message-price' => 20,
                        'client-ref' => 'abcde',
                    ],
                ],
                'code' => 200,
            ],
        ];

        $clientResponse = $this->createMock(Response::class);
        $clientResponse->expects($this->once())->method('getBody')->willReturn(json_encode($response));
        $messageResponse = new \Camoo\Sms\Response\Message($clientResponse);
        $message = $this->createMock(Message::class);
        $message->expects($this->once())->method('send')->willReturn($messageResponse);
        $this->driver->expects($this->once())->method('getDB')->with($this->dbConfig)->willReturn($this->driver);
        $this->driver->expects($this->once())->method('insert')->with('table', [
            'message' => 'Hello world',
            'recipient' => '+237612345678',
            'message_id' => '1661562859237661562194941475',
            'sender' => 'UnitTest',
            'response' => '[]'])->willReturn(true);
        $this->driver->expects($this->once())->method('close')->willReturn(true);
        $handler = new BulkMessageCommandHandler($message);
        $sent = $handler->handle($this->command);
        $this->assertSame(1, iterator_count($sent));
    }

    public function testThrowsBulkSendException(): void
    {
        $resetTime = time() + 2;
        $this->expectException(BulkSendException::class);

        $this->response->expects($this->any())->method('getStatusCode')->will($this->returnValue(429));
        $this->client->expects($this->any())->method('sendRequest')->willReturn($this->response);
        $this->response->expects($this->any())->method('getHeaderLine')
            ->willReturnOnConsecutiveCalls(10, 8, $resetTime);

        $sendHandler = new ExecuteRequestCommandHandler(null, $this->client);
        $message = Message::create('key', 'secret', $sendHandler);

        $handler = new BulkMessageCommandHandler($message);
        iterator_to_array($handler->handle($this->command));
    }
}
