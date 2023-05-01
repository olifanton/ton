<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Olifanton\Mnemonic\TonMnemonic;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;
use Olifanton\Ton\Transports\Toncenter\ToncenterTransport;
use Psr\Log\AbstractLogger;

define("ROOT_DIR", dirname(__DIR__));

require ROOT_DIR . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createMutable(ROOT_DIR);
$dotenv->load();

$httpClient = new HttpMethodsClient(
    HttpClientDiscovery::find(),
    Psr17FactoryDiscovery::findRequestFactory(),
    Psr17FactoryDiscovery::findStreamFactory(),
);

$toncenter = new ToncenterHttpV2Client(
    $httpClient,
    new ClientOptions(
        (defined("MAIN_NET") && MAIN_NET) ?  "https://toncenter.com/api/v2" : "https://testnet.toncenter.com/api/v2",
        $_ENV["TONCENTER_API_KEY"],
    ),
);
$transport = new ToncenterTransport($toncenter);

$words = explode(" ", trim($_ENV["TEST_V3R2_WALLET_WORDS"]));
$kp = TonMnemonic::mnemonicToKeyPair($words);

$logger = new class extends AbstractLogger
{
    public function log($level, Stringable | string $message, array $context = []): void
    {
        $renderContextRow = static function (string $ctxKey, $ctxValue) {
            $strVal = $ctxValue instanceof \Throwable
                ? ($ctxValue->getMessage() . "; (" . $ctxValue->getCode() .  "); file: " . $ctxValue->getFile() . ":" . $ctxValue->getLine())
                : (is_scalar($ctxValue) ? $ctxValue : gettype($ctxValue));

            return "\t\t[$ctxKey] => " . $strVal;
        };

        echo sprintf(
            "[%s] (%s) %s%s%s",
            (new DateTimeImmutable())->format(DATE_RFC3339),
            $level,
            $message,
            empty($context)
                ? ""
                : PHP_EOL . "\tContext:" . PHP_EOL . implode(PHP_EOL, array_map($renderContextRow, array_keys($context), array_values($context))) . PHP_EOL . "\t" . str_repeat('=', 20) . PHP_EOL,
            PHP_EOL,
        );
    }
};
