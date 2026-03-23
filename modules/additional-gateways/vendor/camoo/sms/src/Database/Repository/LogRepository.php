<?php

declare(strict_types=1);

namespace Camoo\Sms\Database\Repository;

use Camoo\Sms\Database\MySQL;
use Camoo\Sms\Entity\DbConfig;
use Camoo\Sms\Interfaces\DriversInterface;
use Camoo\Sms\Interfaces\LogRepositoryInterface;

final class LogRepository implements LogRepositoryInterface
{
    public function __construct(private readonly DbConfig $dbConfig, private readonly ?DriversInterface $driver = null)
    {
    }

    public function save(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $table = $this->dbConfig->tableName;
        if (empty($table)) {
            return false;
        }
        $prefix = $this->dbConfig->prefix ?? '';
        $dbTable = $prefix . $table;
        $driver = $this->driver ?? MySQL::getInstance();
        $result = $driver->getDB($this->dbConfig)->insert($dbTable, $data);

        return $this->driver->close() && $result;
    }
}
