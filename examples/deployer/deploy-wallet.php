<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\DeployOptions;

require dirname(__DIR__) . "/common.php";

global $kp, $transport, $logger;

$deployer = new \Olifanton\Ton\Deployer($transport);
$deployer->setLogger($logger);

$deployWallet = new WalletV3R1(
    new WalletV3Options(
        $kp->publicKey,
    )
);

$newWalletPk = \Olifanton\Ton\Helpers\KeyPair::random();
$newWallet = new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4R2(
    new \Olifanton\Ton\Contracts\Wallets\V4\WalletV4Options(
        publicKey: $newWalletPk->publicKey,
    ),
);

$logger
    ->debug(
        "Deployer address: " . $deployWallet->getAddress()->toString(true, true, false)
    );

$deployOptions = new DeployOptions(
    $deployWallet,
    $kp->secretKey,
    Units::toNano("0.05"),
);
$fee = $deployer->estimateFee($deployOptions, $newWallet);
$logger->debug("Deploy fee: " . $fee->toFloat() . " TON");

$deployer->deploy($deployOptions, $newWallet);

$logger->debug(
    "Verifier url: https://verifier.ton.org/" . $newWallet->getAddress()->toString(true, true, false) . "?testnet="
);

$logger->debug("Done!");
