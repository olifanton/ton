<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\Simple;

use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Wallet;

class SimpleWalletR2 extends AbstractWallet implements Wallet
{
    public static function getName(): string
    {
        return "simpleR2";
    }

    protected static function getHexCodeString(): string
    {
        return "B5EE9C724101010100530000A2FF0020DD2082014C97BA9730ED44D0D70B1FE0A4F260810200D71820D70B1FED44D0D31FD3FFD15112BAF2A122F901541044F910F2A2F80001D31F3120D74A96D307D402FB00DED1A4C8CB1FCBFFC9ED54D0E2786F";
    }
}
