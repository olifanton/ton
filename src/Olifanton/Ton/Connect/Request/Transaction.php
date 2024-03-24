<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Request;

use Olifanton\Interop\Address;
use Olifanton\Ton\Network;

class Transaction implements \JsonSerializable
{
    private array $messages;

    public function __construct(
        private readonly ?int $validUntil,
        private readonly Address|string|null $from,
        private readonly ?Network $network,
        Message ...$message,
    ) {
        $this->messages = $message;
    }

    /**
     * @throws \Throwable
     */
    public function jsonSerialize(): array
    {
        $result = [
            "messages" => array_map(
                static fn (Message $m) => $m->jsonSerialize(),
                $this->messages,
            ),
        ];

        if ($this->validUntil !== null) {
            $result["valid_until"] = $this->validUntil;
        }

        if ($this->network !== null) {
            $result["network"] = (string)$this->network->value;
        }

        if ($this->from !== null) {
            $result["from"] = is_string($this->from) ? $this->from : $this->from->toString();
        }

        return $result;
    }
}
