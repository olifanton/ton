<?php

namespace Olifanton\Ton\Tests\Stubs;

use Olifanton\Ton\Contracts\Messages\ResponseStack;

class StubSeqnoResponseStack
{
    /**
     * @throws \Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException
     */
    public static function create(int $seqno): ResponseStack
    {
        return ResponseStack::parse([
            [
                "num",
                "0x" . dechex($seqno),
            ]
        ]);
    }
}
