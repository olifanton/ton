<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Exceptions\ContractException;

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

    public function getWc(): int;
}
