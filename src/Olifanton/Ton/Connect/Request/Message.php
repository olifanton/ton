<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Request;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\Messages\StateInit;

class Message implements \JsonSerializable
{
    /**
     * @param Cell|string|null $payload Accepts NULL, a serialized BoC string (in Base64 format) or a Cell instance
     * @param StateInit|string|null $stateInit Accepts NULL, a serialized BoC string (in Base64 format) or a StateInit instance
     */
    public function __construct(
        private readonly Address|string $address,
        private readonly BigInteger $amount,
        private readonly Cell|string|null $payload = null,
        private readonly StateInit|string|null $stateInit = null,
    ) {}

    /**
     * @throws \Olifanton\Interop\Boc\Exceptions\CellException
     * @throws \Olifanton\Ton\Contracts\Messages\Exceptions\MessageException
     */
    public function jsonSerialize(): array
    {
        $result = [
            "address" => is_string($this->address) ? $this->address : $this->address->toString(),
            "amount" => $this->amount->toBase(10),
        ];

        if ($this->payload !== null) {
            $result["payload"] = is_string($this->payload)
                ? $this->payload
                : Bytes::bytesToBase64($this->payload->toBoc(false));
        }

        if ($this->stateInit !== null) {
            $result["stateInit"] = is_string($this->stateInit)
                ? $this->stateInit
                : Bytes::bytesToBase64($this->stateInit->cell()->toBoc(false));
        }

        return $result;
    }
}
