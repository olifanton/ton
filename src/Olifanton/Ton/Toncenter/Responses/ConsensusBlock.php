<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class ConsensusBlock
{
    #[JsonMap("consensus_block")]
    public readonly int $consensusBlock;

    #[JsonMap()]
    public readonly float $timestamp;
}
