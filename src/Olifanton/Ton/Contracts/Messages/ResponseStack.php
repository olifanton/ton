<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Brick\Math\BigInteger;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException;

class ResponseStack extends \SplStack
{
    private function __construct()
    {
        $this->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO);
    }

    public static function empty(): self
    {
        return new self();
    }

    /**
     * @throws ResponseStackParsingException
     */
    public static function parse(array $rawStack): self
    {
        $instance = new self();

        foreach ($rawStack as $idx => [$typeName, $value]) {
            switch ($typeName) {
                case 'num':
                    $instance->push(BigInteger::fromBase(
                        str_replace("0x", "", $value),
                        16,
                    ));
                    break;

                case "list":
                case "tuple":
                    $instance->push(
                        array_map(static fn (array $entry) => self::parseObject($entry), $value)
                    );
                    break;

                case "cell":
                    try {
                        $instance->push(Cell::oneFromBoc($value, true));
                    } catch (CellException $e) {
                        throw new ResponseStackParsingException(
                            sprintf(
                                "Cell deserialization error: %s; stack index: %u",
                                $e->getMessage(),
                                $idx,
                            ),
                            $e->getCode(),
                            $e,
                        );
                    }
                    break;

                default:
                    throw new ResponseStackParsingException(
                        "Unknown type: " . $typeName,
                    );
            }
        }

        return $instance;
    }

    /**
     * @throws ResponseStackParsingException
     */
    private static function parseObject(array $entry): mixed
    {
        $typeName = $entry['@type'];

        try {
            return match ($typeName) {
                'tvm.list', 'tvm.tuple' => array_map(static fn(array $e) => self::parseObject($e), $entry['elements']),
                'tvm.cell' => Cell::oneFromBoc($entry['bytes'], true),
                'tvm.stackEntryCell' => self::parseObject($entry['cell']),
                'tvm.stackEntryTuple' => self::parseObject($entry['tuple']),
                'tvm.stackEntryNumber' => self::parseObject($entry['number']),
                'tvm.numberDecimal' => BigInteger::fromBase($entry['number'], 10),
                default => throw new ResponseStackParsingException(
                    "Unknown type: " . $typeName,
                ),
            };
        } catch (CellException $e) {
            throw new ResponseStackParsingException(
                sprintf(
                    "Cell deserialization error: %s",
                    $e->getMessage(),
                ),
                $e->getCode(),
                $e,
            );
        }
    }

    public function currentBigInteger(): ?BigInteger
    {
        $current = $this->current();

        if ($current instanceof BigInteger) {
            return $current;
        }

        return null;
    }
}
