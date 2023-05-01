<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Olifanton\Interop\Address;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\JettonOffchainMetadataFetcher;

require dirname(__DIR__) . "/common.php";

global $httpClient, $transport, $logger;

$metadataFetcher = new JettonOffchainMetadataFetcher($transport, $httpClient);

$minter = JettonMinter::fromAddress($transport, new Address("EQDOTr21oyFE5Aylolo0NxG9eNxSxPz0bW2OWzaswOrP7WTK"));
$metadata = $metadataFetcher->getMetadata($minter);

$logger->info("Metadata: " . json_encode($metadata, JSON_PRETTY_PRINT));
