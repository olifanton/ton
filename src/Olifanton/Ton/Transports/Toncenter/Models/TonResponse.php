<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Models;

class TonResponse
{
    public function __construct(
        public readonly bool   $ok,
        public readonly mixed  $result,
        public readonly ?string $error,
        public readonly ?int    $code,
    ) {}
}
