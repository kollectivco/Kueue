<?php

declare(strict_types=1);

namespace Camoo\Sms\Interfaces;

use Camoo\Sms\Entity\DbConfig;

interface DriversInterface
{
    public static function getInstance(array $options = []): self;

    public function insert(string $table, array $variables = []): bool;

    public function close(): bool;

    public function getDB(?DbConfig $dbConfig = null): ?self;
}
