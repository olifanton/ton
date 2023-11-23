<?php declare(strict_types=1);

namespace Olifanton\Ton\Marshalling\Tvm;

// {
// '@type': 'tvm.stackEntryCell',
// 'cell': {
//  '@type': 'tvm.Cell',
//  'bytes': "base64 BoC"
//  }
// }

class Cell extends TvmStackEntry
{
    public function __construct(\Olifanton\Interop\Boc\Cell $data)
    {
        parent::__construct("tvm.stackEntryCell", $data);
    }

    public function getData(): \Olifanton\Interop\Boc\Cell
    {
        return $this->data;
    }
}
