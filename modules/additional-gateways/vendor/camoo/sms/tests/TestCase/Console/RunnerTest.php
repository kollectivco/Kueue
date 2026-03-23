<?php

namespace CamooSms\Test\TestCase\Console;

use Camoo\Sms\Console\BulkMessageCommand;
use Camoo\Sms\Console\BulkMessageCommandHandler;
use Camoo\Sms\Console\Runner;
use Camoo\Sms\Constants;
use Camoo\Sms\Lib\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Class RunnerTest
 *
 * @author CamooSarl
 *
 * @covers \Camoo\Sms\Console\Runner
 */
class RunnerTest extends TestCase
{
    private $sTmpName;

    private $sTmpFile;

    private ?BulkMessageCommandHandler $handler;

    protected function setUp(): void
    {
        $this->sTmpName = 'test' . Utils::randomStr() . '.bulk';
        $this->sTmpFile = Constants::getSMSPath() . 'tmp/' . $this->sTmpName;
        $this->handler = $this->createMock(BulkMessageCommandHandler::class);
    }

    public function tearDown(): void
    {
        if (file_exists($this->sTmpFile)) {
            unlink($this->sTmpFile);
        }
    }

    public function testWithoutCommand(): void
    {
        $argv = [
            'php',
        ];
        $runner = new Runner($this->handler);
        $runner->run($argv);
        $this->handler->expects($this->never())->method('handle');
    }

    public function testWithoutArguments(): void
    {
        $argv = [
            'php',
            'line',
        ];
        $runner = new Runner($this->handler);
        $runner->run($argv);
        $this->handler->expects($this->never())->method('handle');
    }

    public function testWithMissingTmpFile(): void
    {
        $line = json_encode([[], 'fooBar', ['api_key' => 'key', 'api_secret' => 'secret']]);
        $argv = [
            'php',
            base64_encode($line),
        ];
        $runner = new Runner($this->handler);
        $runner->run($argv);
        $this->handler->expects($this->never())->method('handle');
    }

    /** @covers \Camoo\Sms\Console\Runner::run */
    public function testCanRun(): void
    {
        $hData = [
            'to' => ['+237612345611'],
            'message' => 'foo bar',
            'from' => 'Foo',
        ];
        file_put_contents($this->sTmpFile, json_encode($hData));
        $sPASS = json_encode([[], $this->sTmpName, ['api_key' => 'key', 'api_secret' => 'secret']]);
        $argv = [
            'php',
            base64_encode($sPASS),
        ];
        $command = new BulkMessageCommand($hData, null, null, null, null);
        $this->handler->expects($this->once())->method('handle')->with($command);
        $oRunner = new Runner($this->handler);
        $oRunner->run($argv);
    }
}
