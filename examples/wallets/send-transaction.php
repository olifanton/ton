<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Contracts\Wallets\TransferMessageOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;
use Olifanton\Ton\Transports\Toncenter\ToncenterTransport;

require dirname(__DIR__, 2) . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createMutable(__DIR__);
$dotenv->load();

$toncenter = new ToncenterHttpV2Client(
    new HttpMethodsClient(
        HttpClientDiscovery::find(),
        Psr17FactoryDiscovery::findRequestFactory(),
        Psr17FactoryDiscovery::findStreamFactory(),
    ),
    new ClientOptions(
        "https://testnet.toncenter.com/api/v2",
        $_ENV["TONCENTER_API_KEY"],
    ),
);
$transport = new ToncenterTransport($toncenter);

$words = explode(" ", trim($_ENV["TEST_V3R2_WALLET_WORDS"]));
$kp = TonMnemonic::mnemonicToKeyPair($words);

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
    )
);

$transport->sendMessage($extMsg, $kp->secretKey);
