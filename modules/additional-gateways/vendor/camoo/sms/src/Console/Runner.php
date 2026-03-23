<?php

declare(strict_types=1);

namespace Camoo\Sms\Console;

use Camoo\Sms\Constants;
use Camoo\Sms\Entity\Credential;
use Camoo\Sms\Lib\Utils;
use Camoo\Sms\Message;

class Runner
{
    public function __construct(private readonly ?BulkMessageCommandHandler $bulkMessageCommandHandler = null)
    {
    }

    public function run(array $argv): void
    {
        if (empty($argv[1])) {
            return;
        }

        $command = $argv[1];
        $arguments = Utils::decodeJson(base64_decode($command) ?: '', true);
        if (empty($arguments) || count($arguments) < 3) {
            return;
        }

        [$callback, $sTmpName, $credentials] = $arguments;
        $tmpFile = Constants::getSMSPath() . 'tmp/' . $sTmpName;

        if (!is_file($tmpFile) || !is_readable($tmpFile)) {
            return;
        }

        $tmpContent = file_get_contents($tmpFile);

        if (!empty($tmpContent) && ($bulkData = Utils::decodeJson($tmpContent, true))) {
            unlink($tmpFile);
            $this->applyBulk($credentials, $bulkData, $callback);
        }
    }

    private function applyBulk(array $credentials, array $bulkData, array $callback): void
    {
        $credentials = new Credential(
            $credentials['api_key'],
            $credentials['api_secret'],
        );
        $command = new BulkMessageCommand(
            $bulkData,
            $callback['driver'] ?? null,
            $callback['db_config'] ?? null,
            $callback['tableMapping'] ?? null,
            $callback['bulk_chunk'] ?? null
        );
        $handler = $this->bulkMessageCommandHandler ??
            new BulkMessageCommandHandler(Message::create($credentials->key, $credentials->secret));
        $generator = $handler->handle($command);

        foreach ($generator as $message) {
            if ($message->getId()) {
                echo 'MessageId is: ' . $message->getId() . PHP_EOL;
            }
        }
    }
}
