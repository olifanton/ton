<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\TypedArrays\Uint8Array;

interface Contract
{
    public static function getName(): string;

    /**
     * @throws ContractException
     */
    public function getCode(): Cell;

    /**
     * @throws ContractException
     */
    public function getData(): Cell;

    /**
     * @throws ContractException
     */
    public function getAddress(): Address;

    public function getPublicKey(): Uint8Array;

    public function getWc(): int;
}
