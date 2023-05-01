<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Olifanton\Interop\Units;
use Olifanton\Ton\ContractAwaiter;
use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonMinterOptions;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
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
        jettonWalletCode: (new JettonWallet(new ContractOptions(null)))->getCode(),
    ),
);
// EQDOTr21oyFE5Aylolo0NxG9eNxSxPz0bW2OWzaswOrP7WTK

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

$logger->info("Done!");
