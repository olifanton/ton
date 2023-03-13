<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Bytes;
use Olifanton\Interop\Crypto;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\ContractOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\DeployOptions;
use Olifanton\TypedArrays\Uint8Array;

require __DIR__ . "/common.php";

global $kp, $transport, $logger;

$deployer = new \Olifanton\Ton\Deployer($transport);
$deployer->setLogger($logger);

$wallet = new WalletV3R1(
    new WalletV3Options(
        $kp->publicKey,
    )
);

$exampleContractPk = Crypto::keyPairFromSeed(new Uint8Array(Bytes::bytesToArray(random_bytes(SODIUM_CRYPTO_SIGN_SEEDBYTES))));
$exampleContract = new class(new ContractOptions(publicKey: $exampleContractPk->publicKey)) extends AbstractContract
{
    protected function createCode(): Cell
    {
        // Compiled BoC from Blueprint's simple counter contract
        return Cell::oneFromBoc("b5ee9c7241010a010089000114ff00f4a413f4bcf2c80b01020162050202016e0403000db63ffe003f0850000db5473e003f08300202ce070600194f842f841c8cb1fcb1fc9ed5480201200908001d3b513434c7c07e1874c7c07e18b46000671b088831c02456f8007434c0cc1c6c244c383c0074c7f4cfcc4060841fa1d93beea6f4c7cc3e1080683e18bc00b80c2103fcbc208d7eb34a");
    }

    protected function createData(): Cell
    {
        $data = new Cell();
        $bs = $data->bits;

        $bs
            ->writeUint(100500, 32) // ctx_id
            ->writeUint(100, 32); // ctx_counter

        return $data;
    }

    public static function getName(): string
    {
        return "example";
    }
};

/*
$fee = $deployer->estimateFee($exampleContract);
$logger->debug("Deploy fee: " . Units::fromNano($fee));
*/

$deployer->deploy(
    new DeployOptions(
        $wallet,
        $kp->secretKey,
        Units::toNano("0.05"),
    ),
    $exampleContract,
);

$logger->debug("Done!");
