<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Replies;

use Olifanton\Interop\Address;
use Olifanton\Ton\Marshalling\Attributes\JsonMap;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\Ton\Network;

class TonAddr extends Reply implements \JsonSerializable
{
    #[JsonMap("address")]
    public readonly string $address;

    #[JsonMap("network", JsonMap::SER_TYPE, "int")]
    public readonly int $network;

    #[JsonMap("publicKey")]
    public readonly string $publicKey;

    #[JsonMap("walletStateInit")]
    public readonly string $walletStateInit;

    public function getName(): string
    {
        return "ton_addr";
    }

    public function getAddress(): Address
    {
        return new Address($this->address);
    }

    public function getNetwork(): Network
    {
        return Network::from($this->network);
    }

    public function jsonSerialize(): array
    {
        return [
            "address" => $this->address,
            "network" => $this->network,
            "publicKey" => $this->publicKey,
            "walletStateInit" => $this->walletStateInit,
        ];
    }

    /**
     * @throws \JsonException
     * @throws \Olifanton\Ton\Marshalling\Exceptions\MarshallingException
     */
    public static function restore(array|string $json): self
    {
        return Hydrator::extract(
            self::class,
            is_string($json) ? json_decode($json, true, flags: JSON_THROW_ON_ERROR) : $json,
        );
    }
}
