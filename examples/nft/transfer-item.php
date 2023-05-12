<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Nft\NftItem;
use Olifanton\Ton\Contracts\Nft\NftTransferOptions;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;

require dirname(__DIR__) . "/common.php";

global $kp, $transport, $logger;

$itemAddress = new Address("EQCE8BzG54gmMvsJSvXjVsm-i2Ul71ijQUi6znIaQvrph0gL");

$ownerWallet = new WalletV3R1(
    new WalletV3Options(
        publicKey: $kp->publicKey,
    )
);

$external = $ownerWallet->createTransferMessage(
    [
        new Transfer(
            $itemAddress,
            Units::toNano("0.01"),
            NftItem::createTransferBody(
                new NftTransferOptions(
                    new Address("EQBYivdc0GAk-nnczaMnYNuSjpeXu2nJS3DZ4KqLjosX5sVC"),
                )
            )
        ),
    ],
    new TransferOptions(
        seqno: (int)$ownerWallet->seqno($transport),
    ),
);

$transport->sendMessage($external, $kp->secretKey);
$logger->info("Done!");
