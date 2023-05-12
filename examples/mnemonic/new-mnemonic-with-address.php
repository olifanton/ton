<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;

require dirname(__DIR__) . "/common.php";

global $logger;

$mnemonic = TonMnemonic::generate();
$logger->info("New phrase: " . implode(" ", $mnemonic));
$kp = TonMnemonic::mnemonicToKeyPair($mnemonic);

/** @var class-string<WalletV3>[] $wallets */
$wallets = [
    WalletV3R1::class,
    WalletV3R2::class,
];

foreach ($wallets as $wallet) {
    /** @var WalletV3 $smc */
    $smc = new $wallet(new WalletV3Options(
        publicKey: $kp->publicKey,
    ));

    $logger->info(sprintf(
        "Address [%s]: %s",
        $smc::getName(),
        $smc->getAddress()->toString(true, true, false),
    ));
}
