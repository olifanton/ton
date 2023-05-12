<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Olifanton\Interop\Units;
use Olifanton\Ton\ContractAwaiter;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonMinterOptions;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Contracts\Jetton\MintOptions;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Deployer;
use Olifanton\Ton\DeployOptions;

require dirname(__DIR__) . "/common.php";

global $kp, $transport, $logger;

$awaiter = new ContractAwaiter($transport);
$awaiter->setLogger($logger);

// Admin and deployer wallet
$deployWallet = new WalletV3R1(
    new WalletV3Options(
        $kp->publicKey,
    ),
);

// Minter instance
$minter = new JettonMinter(
    new JettonMinterOptions(
        adminAddress: $deployWallet->getAddress(),
        jettonContentUrl: "https://api.npoint.io/036c97bf516d3996c9b0",
        jettonWalletCode: JettonWallet::getDefaultCode(),
    ),
);

// Deploy new minter contract
$deployer = new Deployer($transport);
$deployer->setLogger($logger);
$deployer->deploy(
    new DeployOptions(
        $deployWallet,
        $kp->secretKey,
        Units::toNano(0.5),
    ),
    $minter,
);

$awaiter->waitForActive($minter->getAddress());

$transfer = $deployWallet->createTransferMessage(
    [
        new Transfer(
            dest: $minter->getAddress(),
            amount: Units::toNano("0.05"),
            payload: JettonMinter::createMintBody(new MintOptions(
                jettonAmount: Units::toNano("1000000"),
                destination: $deployWallet->getAddress(),
                amount: Units::toNano("0.05"),
            )),
            bounce: false,
        ),
    ],
    new TransferOptions(
        seqno: (int)$deployWallet->seqno($transport),
    )
);
$transport->sendMessage($transfer, $kp->secretKey);

$jettonWalletAddress = $minter->getJettonWalletAddress($transport, $deployWallet->getAddress());
$logger->info(
    "Jetton wallet address: " . $jettonWalletAddress->toString(true, true, false),
);

$logger->info("Done!");
