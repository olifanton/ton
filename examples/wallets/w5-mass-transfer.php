<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\V5\WalletV5Options;
use Olifanton\Ton\Contracts\Wallets\V5\WalletV5R1;
use Olifanton\Ton\Contracts\Wallets\V5\WalletV5TransferOptions;
use Olifanton\Ton\SendMode;

require dirname(__DIR__) . "/common.php";

global $kp, $transport, $logger;

$wallet = new WalletV5R1(
    new WalletV5Options(
        $kp->publicKey,
    ),
);

$logger->info(
    "Sending from " . $wallet->getAddress()->toString(true, true, false)
);
$transfers = [];

for ($i = 0; $i < 128; $i++) {
    $transfers[] = new Transfer(
        dest: new Address("EQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAM9c"),
        amount: Units::toNano(0.001),
        sendMode: SendMode::IGNORE_ERRORS->combine(SendMode::PAY_GAS_SEPARATELY), // <- This is important for W5
    );
}

$extMessage = $wallet
    ->createTransferMessage(
        $transfers,
        new WalletV5TransferOptions(
            seqno: (int)$wallet->seqno($transport),
        ),
    );

$transport->sendMessage($extMessage, $kp->secretKey);
