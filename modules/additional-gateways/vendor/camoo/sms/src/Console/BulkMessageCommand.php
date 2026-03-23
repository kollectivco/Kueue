<?php

declare(strict_types=1);

namespace Camoo\Sms\Console;

use Camoo\Sms\Constants;
use Camoo\Sms\Entity\DbConfig;
use Camoo\Sms\Entity\TableMapping;
use Camoo\Sms\Interfaces\DriversInterface;

final class BulkMessageCommand
{
    public function __construct(
        public readonly array $data,
        public readonly ?DriversInterface $driver = null,
        public readonly ?DbConfig $dbConfig = null,
        public readonly ?TableMapping $tableMapping = null,
        public readonly ?int $bulkChunkLimit = Constants::SMS_MAX_RECIPIENTS,
    ) {
    }
}
