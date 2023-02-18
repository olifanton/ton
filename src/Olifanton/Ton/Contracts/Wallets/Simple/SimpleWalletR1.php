<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\Simple;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Wallet;

class SimpleWalletR1 extends AbstractWallet implements Wallet
{
    public static function getName(): string
    {
        return "simpleR1";
    }

    protected function createCode(): Cell
    {
        return self::deserializeCode("B5EE9C72410101010044000084FF0020DDA4F260810200D71820D70B1FED44D0D31FD3FFD15112BAF2A122F901541044F910F2A2F80001D31F3120D74A96D307D402FB00DED1A4C8CB1FCBFFC9ED5441FDF089");
    }
}
