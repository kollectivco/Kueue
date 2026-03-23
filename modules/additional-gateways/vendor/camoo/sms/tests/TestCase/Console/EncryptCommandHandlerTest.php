<?php

declare(strict_types=1);

namespace CamooSms\Test\TestCase\Console;

use Camoo\Sms\Console\EncryptCommand;
use Camoo\Sms\Console\EncryptCommandHandler;
use Camoo\Sms\Constants;
use Exception;
use nicoSWD\GPG\GPG;
use PHPUnit\Framework\TestCase;

class EncryptCommandHandlerTest extends TestCase
{
    public function testCanCreateInstance(): void
    {
        $this->assertInstanceOf(EncryptCommandHandler::class, new EncryptCommandHandler(new GPG()));
    }

    /** @throws Exception */
    public function testFileDoesNotExist(): void
    {
        $command = new EncryptCommand(sys_get_temp_dir(), 'test');
        $handler = new EncryptCommandHandler();
        $this->assertSame('test', $handler->handle($command));
    }

    /***
     * @throws Exception
     */
    public function testFileContentIsEmpty(): void
    {
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pub.perm';

        $command = new EncryptCommand($file, 'Empty');
        $handler = new EncryptCommandHandler();
        $this->assertSame('Empty', $handler->handle($command));
    }

    /***
     * @throws Exception
     */
    public function testCanHandle(): void
    {
        $file =
        dirname(__DIR__, 3) . Constants::DS .
        'config' . Constants::DS . 'keys' . Constants::DS . 'cert.pem';
        $command = new EncryptCommand($file, 'Message');
        $gpg = $this->createMock(GPG::class);
        $gpg->expects($this->once())->method('encrypt')->willReturn('Encrypted');
        $handler = new EncryptCommandHandler($gpg);
        $this->assertSame('Encrypted', $handler->handle($command));
    }
}
