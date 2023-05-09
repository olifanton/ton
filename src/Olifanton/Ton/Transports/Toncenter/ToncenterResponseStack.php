<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter;

use Brick\Math\BigInteger;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException;
use Olifanton\Ton\Contracts\Messages\ResponseStack;

class ToncenterResponseStack extends \SplQueue implements ResponseStack
{
    private const TYPE_NUM = "num";

    private const TYPE_LIST = "list";

    private const TYPE_TUPLE = "tuple";

    private const TYPE_CELL = "cell";

    private ?array $rawStack;

    /**
     * @throws ResponseStackParsingException
     */
    public static function parse(array $rawStack): self
    {
        $instance = new self();
        $instance->rawStack = $rawStack;
        $instance->parseInternal($rawStack);

        return $instance;
    }

    /**
     * @throws ResponseStackParsingException
     */
    protected function parseInternal(array $rawStack): void
    {
        foreach ($rawStack as $idx => [$typeName, $value]) {
            switch ($typeName) {
                case self::TYPE_NUM:
                    $this->push([
                        $typeName,
                        BigInteger::fromBase(
                            str_replace("0x", "", $value),
                            16,
                        ),
                    ]);
                    break;

                case self::TYPE_LIST:
                case self::TYPE_TUPLE:
                    $this->push([
                        $typeName,
                        array_map(static fn (array $entry) => self::parseObject($entry), $value["elements"]),
                    ]);
                    break;

                case self::TYPE_CELL:
                    try {
                        $this->push([
                            $typeName,
                            Cell::oneFromBoc($value["bytes"], true),
                        ]);
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

        $this->rewind();
    }

    public function currentBigInteger(): ?BigInteger
    {
        return $this->currentInternal(self::TYPE_NUM);
    }

    public function currentList(): ?array
    {
        return $this->currentInternal(self::TYPE_LIST);
    }

    public function currentTuple(): ?array
    {
        return $this->currentInternal(self::TYPE_TUPLE);
    }

    public function currentCell(): ?Cell
    {
        return $this->currentInternal(self::TYPE_CELL);
    }

    public function current(): BigInteger|array|Cell|null
    {
        $curr = parent::current();

        if (is_array($curr)) {
            [$type, $currentValue] = $curr;

            return $currentValue;
        }

        return null;
    }

    public static function empty(): self
    {
        return new self();
    }

    private function __construct() {}

    /**
     * @throws ResponseStackParsingException
     */
    private static function parseObject(array $entry): mixed
    {
        $typeName = $entry["@type"];

        try {
            return match ($typeName) {
                "tvm.list", "tvm.tuple" => array_map(static fn(array $e) => self::parseObject($e), $entry["elements"]),
                "tvm.cell" => Cell::oneFromBoc($entry["bytes"], true),
                "tvm.stackEntryCell" => self::parseObject($entry["cell"]),
                "tvm.stackEntryTuple" => self::parseObject($entry["tuple"]),
                "tvm.stackEntryNumber" => self::parseObject($entry["number"]),
                "tvm.numberDecimal" => BigInteger::fromBase($entry["number"], 10),
                default => throw new ResponseStackParsingException(
                    "Unknown type: " . $typeName,
                ),
            };
        // @codeCoverageIgnoreStart
        } catch (CellException $e) {
            throw new ResponseStackParsingException(
                sprintf(
                    "Cell deserialization error: %s; type: %s",
                    $e->getMessage(),
                    $typeName,
                ),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private function currentInternal(string $type): mixed
    {
        $current = parent::current();

        if (is_array($current)) {
            [$currentType, $currentValue] = $current;

            if ($currentType === $type) {
                return $currentValue;
            }
        }

        return null;
    }

    public function __serialize(): array
    {
        return [
            "raw" => $this->rawStack,
        ];
    }

    /**
     * @throws ResponseStackParsingException
     */
    public function __unserialize(array $data): void
    {
        $rawStack = $data["raw"];
        $this->rawStack = $rawStack;
        $this->parseInternal($rawStack);
    }
}
