<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter;

use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Marshalling\Tvm\Cell;
use Olifanton\Ton\Marshalling\Tvm\Number;
use Olifanton\Ton\Marshalling\Tvm\Slice;
use Olifanton\Ton\Marshalling\Tvm\TvmStackEntry;

final class ToncenterStackSerializer
{
    /**
     * @param array[]|TvmStackEntry[] $stack
     * @return array[]
     * @throws CellException
     * @throws SliceException
     */
    public static function serialize(array $stack): array
    {
        $result = [];

        foreach ($stack as $idx => $entry) {
            if ($entry instanceof TvmStackEntry) {
                if ($entry instanceof Cell) {
                    $result[] = self::serializeCell($entry);
                    continue;
                }

                if ($entry instanceof Slice) {
                    $result[] = self::serializeSlice($entry);
                    continue;
                }

                if ($entry instanceof Number) {
                    $result[] = self::serializeNumber($entry);
                    continue;
                }

                throw new \RuntimeException("Not implemented serializer for " . $entry::class);
            } else if (is_array($entry) && array_is_list($entry)) {
                $result[] = $entry;
            } else {
                $givenMessage = is_array($entry) ? "associative array" : gettype($entry); // @phpstan-ignore-line

                throw new \InvalidArgumentException(
                    "Incorrect stack entry, list expected, " . $givenMessage . " given; index: " . $idx
                );
            }
        }

        return $result;
    }

    /**
     * @throws CellException
     */
    private static function serializeCell(Cell $entry): array
    {
        return ["cell", Bytes::bytesToBase64($entry->getData()->toBoc(has_idx: false))];
    }

    /**
     * @throws CellException
     * @throws SliceException
     */
    private static function serializeSlice(Slice $entry): array
    {
        return ["tvm.Slice", Bytes::bytesToBase64(
            (new Builder())->writeSlice($entry->getData())->cell()->toBoc(has_idx: false),
        )];
    }

    private static function serializeNumber(Number $entry): array
    {
        $n = $entry->getData();

        return ["num", "0x" . (is_int($n) ? dechex($n) : $n->toBase(16))];
    }
}
