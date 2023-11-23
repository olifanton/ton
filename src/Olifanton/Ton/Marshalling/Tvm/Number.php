<?php declare(strict_types=1);

namespace Olifanton\Ton\Marshalling\Tvm;

use Brick\Math\BigInteger;

// {
// '@type': 'tvm.stackEntryNumber',
// 'number': {
//  '@type': 'tvm.numberDecimal',
//  'number': "1000"
//  }
// }

class Number extends TvmStackEntry
{
    public function __construct(int|BigInteger $data)
    {
        parent::__construct("tvm.stackEntryNumber", $data);
    }

    public function getData(): int|BigInteger
    {
        return $this->data;
    }
}
