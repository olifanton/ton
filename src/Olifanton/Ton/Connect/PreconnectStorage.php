<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Olifanton\Ton\Connect\Exceptions\StorageException;

interface PreconnectStorage
{
    public const CONNECTED_PREFIX = "olfnt_pconn_connected_";

    /**
     * @throws StorageException
     */
    public function get(string $key): ?Session;

    /**
     * @throws StorageException
     */
    public function set(string $key, Session $value): void;

    /**
     * @throws StorageException
     */
    public function remove(string $key): void;

    public function getConnectedKey(string $preconnectedId): string;
}
