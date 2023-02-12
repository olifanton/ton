<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V3;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;

class WalletV3R1 extends WalletV3
{
    public static function getName(): string
    {
        return "v3r1";
    }

    protected function createCode(): Cell
    {
        try {
            return Cell::oneFromBoc("B5EE9C724101010100620000C0FF0020DD2082014C97BA9730ED44D0D70B1FE0A4F2608308D71820D31FD31FD31FF82313BBF263ED44D0D31FD31FD3FFD15132BAF2A15144BAF2A204F901541055F910F2A3F8009320D74A96D307D402FB00E8D101A4C8CB1FCB1FCBFFC9ED543FBE6EE0");
        } catch (CellException $e) {
            throw new WalletException("Wallet code creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
