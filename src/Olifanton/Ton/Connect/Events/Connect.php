<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Events;

use Olifanton\Ton\Connect\Replies\Device;
use Olifanton\Ton\Connect\Replies\Reply;
use Olifanton\Ton\Connect\Replies\TonAddr;
use Olifanton\Ton\Connect\Replies\TonProof;
use Olifanton\Ton\Connect\Session;
use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\Ton\Marshalling\Json\Hydrator;

class Connect extends SessionEvent
{
    private readonly int $id;

    private readonly ?Device $device;

    /**
     * @var Reply[]
     */
    private readonly array $items;

    /**
     * @throws MarshallingException
     */
    public function __construct(array $data, public readonly Session $session)
    {
        $this->id = (int)($data["id"] ?? null);
        $this->device = isset($data["payload"]["device"])
            ? Hydrator::extract(Device::class, $data["payload"]["device"])
            : null;
        $items = [];

        if (isset($data["payload"]["items"])) {
            foreach ($data["payload"]["items"] as $item) {
                if (isset($item["name"])) {
                    try {
                        $reply = match ($item["name"]) {
                            "ton_proof" => isset($item["proof"])
                                ? Hydrator::extract(TonProof::class, $item["proof"])
                                : null,
                            "ton_addr" => Hydrator::extract(TonAddr::class, $item),
                            default => null,
                        };

                        if ($reply) {
                            $items[] = $reply;
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }

        $this->items = $items;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    /**
     * @return Reply[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @template T
     * @param class-string<T> $replyClass
     * @return (T & Reply)|null
     */
    public function getItem(string $replyClass): ?Reply
    {
        foreach ($this->items as $item) {
            if ($item::class === $replyClass) {
                return $item;
            }
        }

        return null;
    }

    public function getName(): string
    {
        return "connect";
    }
}
