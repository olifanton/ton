<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests;

use Brick\Math\BigInteger;
use GuzzleHttp\Psr7\Response;
use Hamcrest\Core\IsEqual;
use Http\Client\Common\HttpMethodsClientInterface;
use Mockery\MockInterface;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonMinterOptions;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Helpers\OffchainHelper;
use Olifanton\Ton\JettonOffchainMetadataFetcher;
use Olifanton\Ton\Tests\Stubs\PredefinedStack;
use Olifanton\Ton\Transport;
use PHPUnit\Framework\TestCase;

class JettonOffchainMetadataFetcherTest extends TestCase
{
    private Transport|MockInterface $transportMock;

    private HttpMethodsClientInterface|MockInterface $httpMethodsClientMock;

    protected function setUp(): void
    {
        $this->transportMock = \Mockery::mock(Transport::class); // @phpstan-ignore-line
        $this->httpMethodsClientMock = \Mockery::mock(HttpMethodsClientInterface::class); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function getInstance(): JettonOffchainMetadataFetcher
    {
        return new JettonOffchainMetadataFetcher(
            $this->transportMock,
            $this->httpMethodsClientMock,
        );
    }

    /**
     * @throws \Throwable
     */
    public function testGetData(): void
    {
        // Stubs
        $smc = new JettonMinter(new JettonMinterOptions(
            adminAddress: Address::NONE,
            jettonContentUrl: "https://example.com/jetton.json",
            jettonWalletCode: JettonWallet::getDefaultCode(),
        ));

        // Mocks
        $this
            ->transportMock
            ->shouldReceive("runGetMethod")
            ->with(
                IsEqual::equalTo($smc->getAddress()),
                IsEqual::equalTo("get_jetton_data"),
            )
            ->andReturn(new PredefinedStack([
                ["num", Units::toNano(1000000)], // Total supply
                ["num", BigInteger::of(-1)], // Mutable flag
                ["cell", (new Builder()) // Admin address
                    ->writeAddress(new Address("EQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAM9c")) // Burn address
                    ->cell()],
                ["cell", OffchainHelper::createUrlCell("https://example.com/jetton.json")], // Jetton content URL,
                ["cell", JettonWallet::getDefaultCode()], // Wallet code
            ]));

        $this
            ->httpMethodsClientMock
            ->shouldReceive("get")
            ->with(
                "https://example.com/jetton.json"
            )
            ->andReturn(
                new Response(200, [], <<<JSON
                {
                  "address": "0:ce4ebdb5a32144e40ca5a25a343711bd78dc52c4fcf46d6d8e5b36acc0eacfed",
                  "name": "Olifanton",
                  "symbol": "OLFNT",
                  "decimals": "9",
                  "image": "https://cache.tonapi.io/imgproxy/kmZ2_jWomjdmp4Iy7TK19AnEYEqAW7XfvDZq8QCqUxU/rs:fill:200:200:1/g:no/aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL3RvbmtlZXBlci9vcGVudG9uYXBpL21hc3Rlci9wa2cvcmVmZXJlbmNlcy9tZWRpYS90b2tlbl9wbGFjZWhvbGRlci5wbmc.webp",
                  "description": "Olifanton test token"
                }
                JSON),
            );

        // Test
        $metadata = $this->getInstance()->getMetadata($smc);
        $this->assertEquals("Olifanton", $metadata->name);
    }
}
