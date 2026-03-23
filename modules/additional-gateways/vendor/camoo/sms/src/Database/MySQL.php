<?php

declare(strict_types=1);

namespace Camoo\Sms\Database;

use Camoo\Sms\Entity\DbConfig;
use Camoo\Sms\Interfaces\DriversInterface;
use Exception;
use mysqli;
use mysqli_result;

/**
 * Class MySQL
 */
class MySQL implements DriversInterface
{
    private mysqli|null $connection = null;

    private static array $dbOptions = [];

    public function __construct(private readonly ?DbConfig $dbConfig = null, private readonly ?mysqli $mysqli = null)
    {
        $this->setConfig($this->dbConfig);
    }

    public static function getInstance(array $options = []): MySQL
    {
        static::$dbOptions = $options;

        return new self();
    }

    public function getDB(?DbConfig $dbConfig = null): ?self
    {
        if (null !== $dbConfig) {
            $this->setConfig($dbConfig);
        }

        if ($this->connection = $this->dbConnect($this->getConf())) {
            return $this;
        }

        return null;
    }

    public function escapeString(string $string): string
    {
        return $this->connection->escape_string(trim($string));
    }

    public function close(): bool
    {
        return $this->connection->close();
    }

    public function query(string $query): ?mysqli_result
    {
        $result = $this->connection->query($query);

        if (!$result) {
            return null;
        }

        return $result;
    }

    public function insert(string $table, array $variables = []): bool
    {
        //Make sure the array isn't empty
        if (empty($variables)) {
            return false;
        }

        $sql = 'INSERT INTO ' . $this->escapeString($table);
        $fields = [];
        $values = [];
        foreach ($variables as $field => $value) {
            $fields[] = $field;
            $values[] = "'" . $this->escapeString($value) . "'";
        }
        $fields = ' (' . implode(', ', $fields) . ')';
        $values = '(' . implode(', ', $values) . ')';

        $sql .= $fields . ' VALUES ' . $values;
        $query = $this->query($sql);

        return (bool)$query;
    }

    public function getError(): string
    {
        return $this->connection?->error ?? '';
    }

    protected function dbConnect(array $config): ?mysqli
    {
        try {
            $mysqlConnection = $this->mysqli ?? new mysqli(
                $config['db_host'],
                $config['db_user'],
                $config['db_password'],
                $config['db_name'],
                $config['db_port']
            );
        } catch (Exception $exception) {
            echo 'Failed to connect to MySQL: ' . $exception->getMessage() . "\n";

            return null;
        }

        return $mysqlConnection;
    }

    private function setConfig(?DbConfig $dbConfig = null): void
    {
        if (null === $dbConfig) {
            return;
        }

        static::$dbOptions = [
            'db_name' => $dbConfig->dbName,
            'db_user' => $dbConfig->dbUser,
            'db_password' => $dbConfig->password,
            'db_host' => $dbConfig->host,
            'table_sms' => $dbConfig->tableName,
        ];
    }

    private function getConf(): array
    {
        $default = ['db_host' => 'localhost', 'db_port' => 3306];
        static::$dbOptions += $default;

        return static::$dbOptions;
    }
}
