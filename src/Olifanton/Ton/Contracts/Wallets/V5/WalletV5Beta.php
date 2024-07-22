<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V5;

class WalletV5Beta extends WalletV5
{
    protected static function getHexCodeString(): string
    {
        return "b5ee9c7201010101002300084202e4cf3b2f4c6d6a61ea0f2b5447d266785b26af3637db2deee6bcd1aa826f3412"; // O ma G!
    }

    public static function getName(): string
    {
        return "v5_beta";
    }
}
