<?php declare(strict_types=1);

namespace Olifanton\Ton\Marshalling\Tvm;

// {
// '@type': 'tvm.stackEntrySlice',
// 'slice': {
//  '@type': 'tvm.Slice',
//  'bytes': "base64 BoC"
//  }
// }

class Slice extends TvmStackEntry
{
    public function __construct(\Olifanton\Interop\Boc\Slice $data)
    {
        parent::__construct("tvm.stackEntrySlice", $data);
    }

    public function getData(): \Olifanton\Interop\Boc\Slice
    {
        return $this->data;
    }
}
