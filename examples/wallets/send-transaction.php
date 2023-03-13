<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Wallets\TransferMessageOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\SendMode;

require dirname(__DIR__) . "/common.php";

global $kp, $transport;

$wallet = new WalletV3R1(
    new WalletV3Options(
        $kp->publicKey,
    )
);

$extMsg = $wallet->createTransferMessage(
    new TransferMessageOptions(
        dest: new Address("EQBYivdc0GAk-nnczaMnYNuSjpeXu2nJS3DZ4KqLjosX5sVC"),
        amount: Units::toNano("0.01"),
        seqno: (int)$wallet->seqno($transport),
        payload: "Hello world!",
        sendMode: SendMode::IGNORE_ERRORS->combine(SendMode::PAY_GAS_SEPARATELY)
    )
);

$transport->sendMessage($extMsg, $kp->secretKey);
