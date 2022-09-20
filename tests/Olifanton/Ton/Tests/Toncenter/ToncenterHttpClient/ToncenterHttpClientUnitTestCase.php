<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Toncenter\ToncenterHttpClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery\MockInterface;
use Olifanton\Ton\ClientOptions;
use Olifanton\Ton\Toncenter\ToncenterHttpClient;
use Olifanton\Utils\Address;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class ToncenterHttpClientUnitTestCase extends TestCase
{
    protected ClientInterface & MockInterface $httpClientMock;

    protected function setUp(): void
    {
        $this->httpClientMock = \Mockery::mock(ClientInterface::class);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    protected function getInstance(): ToncenterHttpClient
    {
        return new ToncenterHttpClient(
            $this->httpClientMock,
            new ClientOptions(
                baseUri: "https://toncenter.local/api/v2",
                apiKey: "foo-bar",
            ),
        );
    }

    /**
     * @throws \JsonException
     */
    protected function createResponseStub(string|array $body, int $status = 200): ResponseInterface
    {
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        return new Response($status, [], $body);
    }

    /**
     * @throws \JsonException
     */
    protected function createResponseDataStub(string $datafile, int $status = 200): ResponseInterface
    {
        $filePath = STUB_DATA_DIR . "/toncenter-responses/" . $datafile . ".json";

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Stub file " .  $filePath . " not found");
        }

        return $this->createResponseStub(file_get_contents($filePath), $status);
    }

    protected function createAddressStub(): Address
    {
        return new Address("EQD__________________________________________0vo");
    }
}
