<?php /** @noinspection PhpComposerExtensionStubsInspection,SqlNoDataSourceInspection */

declare(strict_types=1);

namespace Olifanton\Ton\Connect\Storages;

use Olifanton\Ton\Connect\Exceptions\StorageException;
use Olifanton\Ton\Connect\PreconnectStorage;
use Olifanton\Ton\Connect\Session;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class PdoPreconnectStorage extends AbstractStorage implements PreconnectStorage, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ?string $driver = null;

    public function __construct(
        private readonly \PDO $connection,
        private readonly string $table = "olifanton_preconnect",
        private readonly string $keyColumn = "session_key",
        private readonly string $dataColumn = "data",
    ) {}

    public function get(string $key): ?Session
    {
        if ($this->connection->getAttribute(\PDO::ATTR_ERRMODE) !== \PDO::ERRMODE_EXCEPTION) {
            $this
                ->logger
                ?->warning(
                    "PDO operates in a mode without exceptions, storage may not work correctly",
                );
        }

        $sql = $this->getSelectSql();

        try {
            $stmt = $this->connection->prepare($sql);
        } catch (\PDOException $e) {
            if ($this->isCreateTableNeeded($e)) {
                $this->createTable();

                return null;
            } else {
                throw $e;
            }
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage(), (int)$e->getCode(), $e);
        }

        $stmt->bindValue(":key", $key);
        $this->executeStmt($stmt);

        try {
            if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                return Session::restore($row[$this->dataColumn]);
            }
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage(), (int)$e->getCode(), $e);
        }

        return null;
    }

    public function set(string $key, Session $value): void
    {
        $sql = $this->getInsertSql();

        try {
            $stmt = $this->connection->prepare($sql);
        } catch (\PDOException $e) {
            if ($this->isCreateTableNeeded($e)) {
                $this->createTable();
            }

            $stmt = $this->connection->prepare($sql);
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage(), (int)$e->getCode(), $e);
        }

        $stmt->bindValue(':key', $key);

        try {
            $stmt->bindValue(':data', json_encode($value, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            throw new StorageException($e->getMessage(), (int)$e->getCode(), $e);
        }

        $this->executeStmt($stmt);
    }

    public function remove(string $key): void
    {
        $sql = $this->getDeleteSql();

        try {
            $stmt = $this->connection->prepare($sql);
        } catch (\PDOException $e) {
            if ($this->isCreateTableNeeded($e)) {
                $this->createTable();
                return;
            }

            throw $e;
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage(), (int)$e->getCode(), $e);
        }

        $stmt->bindValue(":key", $key);
        $this->executeStmt($stmt);
    }

    /**
     * @throws StorageException
     */
    public function createTable(): void
    {
        $sql = match ($driver = $this->getDriver()) {
            "mysql" => "CREATE TABLE $this->table ($this->keyColumn VARCHAR(256) NOT NULL PRIMARY KEY, $this->dataColumn LONGTEXT NOT NULL) COLLATE utf8mb4_bin, ENGINE = InnoDB",
            "sqlite" => "CREATE TABLE $this->table ($this->keyColumn TEXT NOT NULL PRIMARY KEY, $this->dataColumn TEXT NOT NULL)",
            "pgsql" => "CREATE TABLE $this->table ($this->keyColumn VARCHAR(256) NOT NULL PRIMARY KEY, $this->dataColumn TEXT NOT NULL)",
            "oci" => "CREATE TABLE $this->table ($this->keyColumn VARCHAR2(256) NOT NULL PRIMARY KEY, $this->dataColumn LONG)",
            default => throw new StorageException(sprintf("Driver \"%s\" is currently not supported", $driver)),
        };
        $this->connection->exec($sql);
    }

    /**
     * @throws StorageException
     */
    protected final function executeStmt(\PDOStatement $stmt): void
    {
        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            if ($this->isCreateTableNeeded($e)) {
                $this->createTable();

                try {
                    $stmt->execute();
                } catch (\PDOException) {
                    throw new StorageException($e->getMessage(), (int)$e->getCode(), $e);
                }
            } else {
                throw new StorageException($e->getMessage(), (int)$e->getCode(), $e);
            }
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @throws StorageException
     */
    protected function getInsertSql(): string
    {
        $driver = $this->getDriver();

        return match ($driver) {
            "mysql", "oci" => "INSERT INTO $this->table ($this->keyColumn, $this->dataColumn) VALUES (:key, :data) ON DUPLICATE KEY UPDATE $this->dataColumn = VALUES($this->dataColumn)",
            "sqlite", "pgsql" => "INSERT INTO $this->table ($this->keyColumn, $this->dataColumn) VALUES (:key, :data) ON CONFLICT($this->keyColumn) DO UPDATE SET $this->dataColumn = excluded.$this->dataColumn",
            default => throw new StorageException(sprintf("Driver \"%s\" is currently not supported", $driver)),
        };
    }

    protected function getSelectSql(): string
    {
        return "SELECT $this->keyColumn, $this->dataColumn FROM $this->table WHERE $this->keyColumn = :key";
    }

    protected function getDeleteSql(): string
    {
        return "DELETE FROM $this->table WHERE $this->keyColumn = :key";
    }

    protected function getDriver(): string
    {
        return $this->driver ??= $this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    protected function isTableMissing(\PDOException $exception): bool
    {
        $driver = $this->getDriver();
        [$sqlState, $code] = $exception->errorInfo ?? [null, $exception->getCode()];

        return match ($driver) {
            "pgsql" => "42P01" === $sqlState,
            "sqlite" => str_contains($exception->getMessage(), "no such table:"),
            "oci" => 942 === $code,
            "mysql" => 1146 === $code,
            default => false,
        };
    }

    protected function isCreateTableNeeded(\PDOException $exception): bool
    {
        return $this->isTableMissing($exception) && (!$this->connection->inTransaction() || \in_array($this->getDriver(), ["pgsql", "sqlite"], true));
    }
}
