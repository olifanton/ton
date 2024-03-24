<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Storages;

use Olifanton\Ton\Connect\PreconnectStorage;
use Olifanton\Ton\Connect\Session;

class InMemoryPreconnectStorage extends AbstractStorage implements PreconnectStorage
{
    private array $data = [];

    /**
     * @throws \Olifanton\Ton\Connect\Exceptions\SessionException
     */
    public function get(string $key): ?Session
    {
        $data = $this->data[$key] ?? null;

        if ($data) {
            return Session::restore($data);
        }

        return null;
    }

    /**
     * @throws \JsonException
     */
    public function set(string $key, Session $value): void
    {
        $this->data[$key] = json_encode($value, JSON_THROW_ON_ERROR);
    }

    public function remove(string $key): void
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }
}
