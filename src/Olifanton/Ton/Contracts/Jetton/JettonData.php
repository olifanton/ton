<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;

class JettonData
{
    public function __construct(
        public readonly BigInteger $totalSupply,
        public readonly bool $isMutable,
        public readonly ?Address $adminAddress,
        public readonly ?string $jettonContentUrl,
        public readonly ?Cell $jettonContentCell,
        public readonly ?Cell $jettonWalletCode,
    ) {}

    /**
     * @throws \Olifanton\Interop\Boc\Exceptions\CellException
     */
    public function asPrintableArray(): array
    {
        return [
            "totalSupply" => $this->totalSupply->toBase(10),
            "isMutable" => $this->isMutable,
            "adminAddress" => $this->adminAddress?->toString(true, true, false),
            "jettonContentUrl" => $this->jettonContentUrl,
            "jettonContentCell" => $this->jettonContentCell
                ? Bytes::bytesToBase64($this->jettonContentCell->toBoc(false))
                : null,
            "jettonWalletCode" => $this->jettonWalletCode
                ? Bytes::bytesToBase64($this->jettonWalletCode->toBoc(false))
                : null,
        ];
    }
}
