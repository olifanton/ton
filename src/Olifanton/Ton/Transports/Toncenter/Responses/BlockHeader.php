<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter\Responses;

use Olifanton\Ton\Marshalling\Attributes\JsonMap;

class BlockHeader
{
    #[JsonMap(serializer: JsonMap::SER_TYPE, param0: BlockIdExt::class)]
    public readonly BlockIdExt $id;

    #[JsonMap("global_id")]
    public readonly int $globalId;

    #[JsonMap]
    public readonly int $version;

    #[JsonMap]
    public readonly int $flags;

    #[JsonMap("after_merge")]
    public readonly bool $afterMerge;

    #[JsonMap("after_split")]
    public readonly bool $afterSplit;

    #[JsonMap("before_split")]
    public readonly bool $beforeSplit;

    #[JsonMap("want_merge")]
    public readonly bool $wantMerge;

    #[JsonMap("want_split")]
    public readonly bool $wantSplit;

    #[JsonMap("validator_list_hash_short")]
    public readonly int $validatorListHashShort;

    #[JsonMap("catchain_seqno")]
    public readonly int $catchainSeqno;

    #[JsonMap("min_ref_mc_seqno")]
    public readonly int $minRefMcSeqno;

    #[JsonMap("is_key_block")]
    public readonly bool $isKeyBlock;

    #[JsonMap("prev_key_block_seqno")]
    public readonly int $prevKeyBlockSeqno;

    #[JsonMap("start_lt")]
    public readonly string $startLt;

    #[JsonMap("end_lt")]
    public readonly string $endLt;

    #[JsonMap("gen_utime")]
    public readonly int $genUtime;

    #[JsonMap("vert_seqno")]
    public readonly ?int $vertSeqno;

    /**
     * @var BlockIdExt[]
     */
    #[JsonMap("prev_blocks", JsonMap::SER_ARR_OF, BlockIdExt::class)]
    public readonly array $prevBlocks;
}
