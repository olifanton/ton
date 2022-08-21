<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V2;

use Olifanton\Boc\Cell;
use Olifanton\Boc\Exceptions\CellException;
use Olifanton\Ton\Contracts\Wallet;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;

class WalletV2R1 extends AbstractWallet implements Wallet
{
    public static function getName(): string
    {
        return "v2r1";
    }

    protected function createCode(): Cell
    {
        try {
            return Cell::oneFromBoc("B5EE9C724101010100570000AAFF0020DD2082014C97BA9730ED44D0D70B1FE0A4F2608308D71820D31FD31F01F823BBF263ED44D0D31FD3FFD15131BAF2A103F901541042F910F2A2F800029320D74A96D307D402FB00E8D1A4C8CB1FCBFFC9ED54A1370BB6");
        } catch (CellException $e) {
            throw new WalletException("Wallet code creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
