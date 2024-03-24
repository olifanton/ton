<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Request;

use Olifanton\Interop\Address;
use Olifanton\Ton\Network;

class SendTransactionRequest implements \JsonSerializable
{
    private ?int $id = null;

    public function __construct(
        private readonly array $params,
    ) {}

    /**
     * @param Message[] $messages
     * @throws \JsonException
     */
    public static function withTransaction(
        array $messages,
        Address|string|null $from = null,
        ?Network $network = null,
        ?int $validUntil = null,
    ): self
    {
        return new self(
            [
                json_encode(
                    new Transaction(
                        $validUntil,
                        $from,
                        $network,
                        ...$messages,
                    ),
                    flags: JSON_THROW_ON_ERROR,
                ),
            ]
        );
    }

    public function withId(int $id): self
    {
        $instance = clone $this;
        $instance->id = $id;

        return $instance;
    }

    public function jsonSerialize(): array
    {
        if ($this->id === null) {
            throw new \RuntimeException("Data integrity error. It is necessary to set the request id");
        }

        return [
            "method" => "sendTransaction",
            "id" => (string)$this->id,
            "params" => $this->params,
        ];
    }
}
