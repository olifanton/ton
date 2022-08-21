<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Boc\Cell;
use Olifanton\Ton\Contract;

abstract class AbstractContract implements Contract
{
    protected ?Cell $code = null;

    protected ?Cell $data = null;

    protected abstract function createCode(): Cell;

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
