<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;

abstract class AbstractContract implements Contract
{
    protected ?Cell $code = null;

    protected ?Cell $data = null;

    /**
     * @throws ContractException
     */
    protected abstract function createCode(): Cell;

    /**
     * @throws ContractException
     */
    protected abstract function createData(): Cell;

    public function getCode(): Cell
    {
        if (!$this->code) {
            $this->code = $this->createCode();
        }

        return $this->code;
    }

    public function getData(): Cell
    {
        if (!$this->data) {
            $this->data = $this->createData();
        }

        return $this->data;
    }
}
