<?php /** @noinspection PhpIncludeInspection,PhpUnhandledExceptionInspection,PhpComposerExtensionStubsInspection */

declare(strict_types=1);

use Olifanton\Interop\Units;
use Olifanton\Ton\ContractAwaiter;
use Olifanton\Ton\Contracts\Nft\MintOptions;
use Olifanton\Ton\Contracts\Nft\NftAttribute;
use Olifanton\Ton\Contracts\Nft\NftCollection;
use Olifanton\Ton\Contracts\Nft\NftCollectionMetadata;
use Olifanton\Ton\Contracts\Nft\NftCollectionOptions;
use Olifanton\Ton\Contracts\Nft\NftItem;
use Olifanton\Ton\Contracts\Nft\NftItemMetadata;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R1;
use Olifanton\Ton\Deployer;
use Olifanton\Ton\DeployOptions;

require dirname(__DIR__) . "/common.php";

global $kp, $httpClient, $transport, $logger;

/**
 * @return string Url
 * @throws JsonException
 * @throws \Http\Client\Exception
 */
function putJsonToJsonbin(JsonSerializable|array $json): string
{
    global $httpClient;

    $response = $httpClient->post(
        "https://api.jsonbin.io/v3/b",
        [
            "Content-Type" => "application/json",
            "X-Access-Key" => $_ENV["JSONBIN_API_KEY"],
            "X-Bin-Private" => "false",
        ],
        json_encode($json, JSON_THROW_ON_ERROR),
    );
    $responseBody = $response->getBody()->getContents();
    $responseJson = json_decode($responseBody, true, flags: JSON_THROW_ON_ERROR);

    if (isset($responseJson["metadata"]["id"])) {
        return "https://api.jsonbin.io/v3/b/" . $responseJson["metadata"]["id"] . "?meta=false";
    }

    throw new \RuntimeException("Invalid response: " . $responseBody);
}

$ownerWallet = new WalletV3R1(
    new WalletV3Options(
        $kp->publicKey,
    ),
);

$deployer = new Deployer($transport);
$deployer->setLogger($logger);

$awaiter = new ContractAwaiter($transport);
$awaiter->setLogger($logger);

$collectionMetadata = new NftCollectionMetadata(
    "Abstracts",
    "Test NFT collection",
    "https://ipfs.io/ipfs/bafybeieb6fimrcevkrpxpz6lmyudhomiseeayzbevhemxfrqxllrujd7s4",
);
$itemsMetadata = [
    new NftItemMetadata(
        "Window",
        "Window abstract photo",
        "https://ipfs.io/ipfs/bafybeieb6fimrcevkrpxpz6lmyudhomiseeayzbevhemxfrqxllrujd7s4",
        attributes: [],
    ),
    new NftItemMetadata(
        "Steps",
        "Snow steps abstract photo",
        "https://ipfs.io/ipfs/QmSJ4cJ5vyqMG4JLqDEK4HaNwd5QfANqsdnkxP5pgHeWHY",
        attributes: [
            new NftAttribute("Nature", "Yes"),
        ],
    ),
    new NftItemMetadata(
        "Rabbit",
        "Rabbit abstract photo",
        "https://ipfs.io/ipfs/QmUJxLV6Gjrzny3TpLhbvDPwPuYBXbSQJj9WgWbm3YaXpB",
        attributes: [
            new NftAttribute("Nature", "Yes"),
            new NftAttribute("Animal", "Yes"),
        ],
    ),
    new NftItemMetadata(
        "Mannequin",
        "Mannequin abstract photo",
        "https://ipfs.io/ipfs/QmXXxZJ3PXZE9XbB2EMhq83VGgv7nkzRYMoav1t2K7YveD",
        attributes: [
            new NftAttribute("Nature", "Yes"),
        ],
    ),
    new NftItemMetadata(
        "Sands",
        "Sands abstract photo",
        "https://ipfs.io/ipfs/QmaC35VmUYybswq5Mvog8kZKUckth3dDMYyptqsjrgsxab",
        attributes: [],
    ),
];

// Upload metadata JSON
$collectionMetadataUrl = putJsonToJsonbin($collectionMetadata);
$itemsMetadataUrls = array_map(static fn(NftItemMetadata $itemMetadata) => putJsonToJsonbin($itemMetadata), $itemsMetadata);

$collectionContract = new NftCollection(new NftCollectionOptions(
    $ownerWallet->getAddress(),
    $collectionMetadataUrl,
    "",
    NftItem::getDefaultCode(),
));

// Upload collection contract
$deployer->deploy(
    new DeployOptions(
        $ownerWallet,
        $kp->secretKey,
        Units::toNano(1),
    ),
    $collectionContract,
);
$awaiter->waitForActive($collectionContract->getAddress());

foreach ($itemsMetadataUrls as $i => $itemsMetadataUrl) {
    $external = $ownerWallet->createTransferMessage(
        [
            new Transfer(
                $collectionContract->getAddress(),
                Units::toNano(0.55),
                NftCollection::createMintBody(new MintOptions(
                    $i,
                    Units::toNano(0.5),
                    $ownerWallet->getAddress(),
                    $itemsMetadataUrl,
                )),
            )
        ],
        new TransferOptions(
            (int)$ownerWallet->seqno($transport),
        )
    );
    $transport->sendMessage($external, $kp->secretKey);
    $itemAddress = $collectionContract->getNftItemAddress($transport, $i);
    $awaiter->waitForActive($itemAddress);
    $logger->info(
        "Item with index $i minted, address: " . $itemAddress->toString(true, true, true)
    );
}

$logger->info("Done!");
