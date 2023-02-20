<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterHttpClient;

use GuzzleHttp\Psr7\Response;
use Http\Client\Common\HttpMethodsClientInterface;
use Mockery\MockInterface;
use Olifanton\Interop\Address;
use Olifanton\Ton\Transports\Toncenter\ClientOptions;
use Olifanton\Ton\Transports\Toncenter\ToncenterHttpV2Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class ToncenterHttpClientUTestCase extends TestCase
{
    protected HttpMethodsClientInterface & MockInterface $httpClientMock;

    protected function setUp(): void
    {
        $this->httpClientMock = \Mockery::mock(HttpMethodsClientInterface::class); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @throws \JsonException
     */
    public static function getDataStub(string $datafile, bool $asArray = true): string | array
    {
        $filePath = STUB_DATA_DIR . "/toncenter-responses/" . $datafile . ".json";

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Stub file " .  $filePath . " not found");
        }

        $data = file_get_contents($filePath);

        return $asArray ? json_decode($data, true, 512, JSON_THROW_ON_ERROR) : $data;
    }

    protected function getInstance(): ToncenterHttpV2Client
    {
        return new ToncenterHttpV2Client(
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
        return $this->createResponseStub(self::getDataStub($datafile, false), $status);
    }

    protected function createAddressStub(): Address
    {
        return new Address("EQD__________________________________________0vo");
    }

    /**
     * @throws \JsonException
     */
    protected function prepareSendMock(string $dataFile): void
    {
        $response = $this->createResponseDataStub($dataFile);
        $this
            ->httpClientMock
            ->shouldReceive("send")
            ->andReturn($response);
    }
}
