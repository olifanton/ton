<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;

require dirname(__DIR__) . "/common.php";

global $kp, $transport, $logger;

$wallet = new WalletV3R1(
    new WalletV3Options(
        $kp->publicKey,
    )
);

$logger->info(
    "Sending from " . $wallet->getAddress()->toString(true, true, false)
);

$extMsg = $wallet->createTransferMessage(
    [
        new Transfer(
            dest: new Address("UQAoqXsjSOtWhZo9t0Fiss9BIiV34qHo5eU6mx0SL0zQ5do-"),
            amount: Units::toNano("0.011"),
        ),
        new Transfer(
            dest: new Address("EQCrrSblmeNMAw27AXbchzG6MUja9iac7PHjyK3Xn8EMeqbG"),
            amount: Units::toNano("0.012"),
        ),
    ],
    new TransferOptions(
        (int)$wallet->seqno($transport),
    ),
);

$transport->sendMessage($extMsg, $kp->secretKey);
