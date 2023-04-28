<?php

namespace Olifanton\Ton\Tests\Stubs;

use Olifanton\Ton\Contracts\Messages\ResponseStack;
use Olifanton\Ton\Transports\Toncenter\ToncenterResponseStack;

class StubSeqnoResponseStack
{
    /**
     * @throws \Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException
     */
    public static function create(int $seqno): ResponseStack
    {
        return ToncenterResponseStack::parse([
            [
                "num",
                "0x" . dechex($seqno),
            ]
        ]);
    }
}
