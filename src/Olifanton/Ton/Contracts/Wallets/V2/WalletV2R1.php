<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V2;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Wallet;

class WalletV2R1 extends AbstractWallet implements Wallet
{
    public static function getName(): string
    {
        return "v2r1";
    }

    protected function createCode(): Cell
    {
        return self::deserializeCode("B5EE9C724101010100570000AAFF0020DD2082014C97BA9730ED44D0D70B1FE0A4F2608308D71820D31FD31F01F823BBF263ED44D0D31FD3FFD15131BAF2A103F901541042F910F2A2F800029320D74A96D307D402FB00E8D1A4C8CB1FCBFFC9ED54A1370BB6");
    }
}
