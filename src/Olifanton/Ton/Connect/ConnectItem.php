<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

class ConnectItem implements \JsonSerializable
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $payload = null,
    ) {}

    public function jsonSerialize(): array
    {
        $result = [
            "name" => $this->name,
        ];

        if ($this->payload) {
            $result["payload"] = $this->payload;
        }

        return $result;
    }

    public static function tonProof(string $payload): self
    {
        return new self("ton_proof", $payload);
    }
}
