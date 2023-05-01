<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Olifanton\Ton\Dns\DnsClient;

define("MAIN_NET", true);

require dirname(__DIR__) . "/common.php";

global $transport, $logger;

$dns = new DnsClient($transport);
$dns->setLogger($logger);
$domain = $dns->resolve("foundation.ton");

$logger->info(sprintf(
    "Done, `foundation.ton` address is %s",
    $domain?->getWallet()?->toString(true, true, true) // EQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqB2N
));
