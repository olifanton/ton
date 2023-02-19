<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Models;

class JsonRpcResponse
{
    public function __construct(
        public readonly bool   $ok,
        public readonly mixed  $result,
        public readonly ?string $error,
        public readonly ?int    $code,
        public readonly string $jsonrpc,
        public readonly ?string $id,
    )
    {
    }

    public function asTonResponse(): TonResponse
    {
        return new TonResponse(
            $this->ok,
            $this->result,
            $this->error,
            $this->code,
        );
    }
}
