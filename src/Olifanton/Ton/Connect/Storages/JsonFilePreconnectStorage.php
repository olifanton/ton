<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Storages;

use Olifanton\Ton\Connect\Exceptions\StorageException;
use Olifanton\Ton\Connect\PreconnectStorage;
use Olifanton\Ton\Connect\Session;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class JsonFilePreconnectStorage extends AbstractStorage implements PreconnectStorage, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $data = [];
    private bool $isInitialized = false;

    public function __construct(
        private readonly string $filePath,
    ) {}

    public function get(string $key): ?Session
    {
        try {
            $this->ensureDataAndFile();
            $data = $this->data[$key] ?? null;

            if ($data) {
                return Session::restore($data);
            }

            return null;
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function set(string $key, Session $value): void
    {
        try {
            $this->ensureDataAndFile();
            $this->data[$key] = json_encode($value, JSON_THROW_ON_ERROR);
            $this->flush();
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function remove(string $key): void
    {
        try {
            $this->ensureDataAndFile();

            if (isset($this->data[$key])) {
                unset($this->data[$key]);
                $this->flush();
            }
        } catch (\Throwable $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws \JsonException
     */
    private function ensureDataAndFile(): void
    {
        if ($this->isInitialized) {
            return;
        }

        if (!file_exists($this->filePath)) {
            $this->flush();
        } else {
            $tmp = file_get_contents($this->filePath);

            try {
                $data = json_decode($tmp, true, flags: JSON_THROW_ON_ERROR);
                $this->data = $data;
            } catch (\JsonException $e) {
                $this
                    ->logger
                    ->error(sprintf("Bad datafile %s, error: %s", $this->filePath, $e->getMessage()), [
                        "exception" => $e,
                    ]);
            }
        }

        $this->isInitialized = true;
    }

    /**
     * @throws \JsonException
     */
    private function flush(): void
    {
        file_put_contents(
            $this->filePath,
            json_encode($this->data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        );
    }
}
