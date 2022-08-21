<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V2;

use Olifanton\Boc\Cell;
use Olifanton\Ton\Contracts\Wallet;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;

class WalletV2R2 extends AbstractWallet implements Wallet
{
    public static function getName(): string
    {
        return "v2r2";
    }

    /**
     * @throws \Olifanton\Boc\Exceptions\CellException
     */
    protected function createCode(): Cell
    {
        return Cell::oneFromBoc("B5EE9C724101010100630000C2FF0020DD2082014C97BA218201339CBAB19C71B0ED44D0D31FD70BFFE304E0A4F2608308D71820D31FD31F01F823BBF263ED44D0D31FD3FFD15131BAF2A103F901541042F910F2A2F800029320D74A96D307D402FB00E8D1A4C8CB1FCBFFC9ED54044CD7A1");
    }
}
