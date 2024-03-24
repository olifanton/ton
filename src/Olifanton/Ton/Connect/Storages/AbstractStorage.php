<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Storages;

use Olifanton\Ton\Connect\PreconnectStorage;

abstract class AbstractStorage
{
    public function getConnectedKey(string $preconnectedId): string
    {
        return PreconnectStorage::CONNECTED_PREFIX . $preconnectedId;
    }
}
