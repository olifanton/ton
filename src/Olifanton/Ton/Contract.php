<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Olifanton\Boc\Cell;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Utils\Address;

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
}
