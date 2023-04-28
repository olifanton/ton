<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Brick\Math\BigInteger;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Messages\Exceptions\ResponseStackParsingException;

interface ResponseStack extends \Countable
{
    /**
     * @throws ResponseStackParsingException
     */
    public static function parse(array $rawStack): self;

    public function currentBigInteger(): ?BigInteger;

    public function currentList(): ?array;

    public function currentTuple(): ?array;

    public function currentCell(): ?Cell;

    public function current(): BigInteger|array|Cell|null;

    public function next(): void;

    public function __serialize(): array;

    /**
     * @throws ResponseStackParsingException
     */
    public function __unserialize(array $data): void;
}
