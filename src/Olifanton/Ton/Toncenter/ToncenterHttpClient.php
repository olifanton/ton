<?php declare(strict_types=1);

namespace Olifanton\Ton\Toncenter;

use Brick\Math\BigInteger;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use Olifanton\Boc\Cell;
use Olifanton\Boc\Exceptions\CellException;
use Olifanton\Ton\ClientOptions;
use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\Ton\Models\AddressState;
use Olifanton\Ton\Models\JsonRpcResponse;
use Olifanton\Ton\Models\TonResponse;
use Olifanton\Ton\Toncenter\Exceptions\ClientException;
use Olifanton\Ton\Toncenter\Exceptions\TimeoutException;
use Olifanton\Ton\Toncenter\Exceptions\ValidationException;
use Olifanton\Ton\Toncenter\Responses\AddressDetectionResult;
use Olifanton\Ton\Toncenter\Responses\ConsensusBlock;
use Olifanton\Ton\Toncenter\Responses\ExtendedFullAccountState;
use Olifanton\Ton\Toncenter\Responses\FullAccountState;
use Olifanton\Ton\Toncenter\Responses\MasterchainInfo;
use Olifanton\Ton\Toncenter\Responses\TransactionsList;
use Olifanton\Ton\Toncenter\Responses\WalletInformation;
use Olifanton\Ton\ToncenterClient;
use Olifanton\Ton\Version;
use Olifanton\Utils\Address;

class ToncenterHttpClient implements ToncenterClient
{
    public function __construct(private readonly ClientInterface $httpClient, private readonly ClientOptions $options)
    {
    }

    /**
     * @inheritDoc
     */
    public function getAddressInformation(Address $address): FullAccountState
    {
        $response = $this->query([
            "method" => "getAddressInformation",
            "params" => [
                "address" => (string)$address,
            ],
        ]);

        try {
            return Hydrator::extract(FullAccountState::class, $response->result);
        } catch (MarshallingException $e) {
            throw new ClientException(
                "Unable to extract FullAccountState response: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getExtendedAddressInformation(Address $address): ExtendedFullAccountState
    {
        $response = $this
            ->query([
                "method" => "getExtendedAddressInformation",
                "params" => [
                    "address" => (string)$address,
                ],
            ]);

        try {
            return Hydrator::extract(ExtendedFullAccountState::class, $response->result);
        } catch (MarshallingException $e) {
            throw new ClientException(
                "Unable to extract ExtendedFullAccountState response: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getWalletInformation(Address $address): WalletInformation
    {
        $response = $this
            ->query([
                "method" => "getWalletInformation",
                "params" => [
                    "address" => (string)$address,
                ],
            ]);

        try {
            return Hydrator::extract(WalletInformation::class, $response->result);
        } catch (MarshallingException $e) {
            throw new ClientException(
                "Unable to extract WalletInformation response: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getTransactions(Address $address,
                                    ?int $limit = null,
                                    ?int $lt = null,
                                    ?string $hash = null,
                                    ?int $toLt = null,
                                    ?bool $archival = null): TransactionsList
    {
        $params = [
            "address" => (string)$address,
        ];

        if (!is_null($limit)) {
            $params["limit"] = $limit;
        }

        if (!is_null($lt)) {
            $params["lt"] = $lt;
        }

        if (!is_null($hash)) {
            $params["hash"] = $hash;
        }

        if (!is_null($toLt)) {
            $params["to_lt"] = $toLt;
        }

        if (!is_null($archival)) {
            $params["archival"] = $archival;
        }

        $response = $this->query([
            "method" => "getTransactions",
            "params" => $params,
        ]);

        try {
            return Hydrator::extract(TransactionsList::class, ["items" => $response->result]);
        } catch (MarshallingException $e) {
            throw new ClientException(
                "Unable to extract array of Transactions: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getAddressBalance(Address $address): BigInteger
    {
        $response = $this
            ->query([
                "method" => "getAddressBalance",
                "params" => [
                    "address" => (string)$address,
                ],
            ]);

        return BigInteger::fromBase((string)$response->result, 10);
    }

    /**
     * @inheritDoc
     */
    public function getAddressState(Address $address): AddressState
    {
        $response = $this
            ->query([
                "method" => "getAddressState",
                "params" => [
                    "address" => (string)$address,
                ],
            ]);

        if ($state = AddressState::tryFrom($response->result)) {
            return $state;
        }

        return AddressState::UNKNOWN;
    }

    /**
     * @inheritDoc
     */
    public function packAddress(string $rawAddress): string
    {
        $response = $this
            ->query([
                "method" => "packAddress",
                "params" => [
                    "address" => $rawAddress,
                ],
            ]);

        return (string)$response->result;
    }

    /**
     * @inheritDoc
     */
    public function unpackAddress(string $address): string
    {
        $response = $this
            ->query([
                "method" => "unpackAddress",
                "params" => [
                    "address" => $address,
                ],
            ]);

        return (string)$response->result;
    }

    /**
     * @inheritDoc
     */
    public function detectAddress(Address|string $address): AddressDetectionResult
    {
        $response = $this->query([
            "method" => "detectAddress",
            "params" => [
                "address" => (string)$address,
            ],
        ]);

        try {
            return Hydrator::extract(AddressDetectionResult::class, $response->result);
        } catch (MarshallingException $e) {
            throw new ClientException(
                "Unable to extract AddressDetectionResult response: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getMasterchainInfo(): MasterchainInfo
    {
        $response = $this->query([
            "method" => "getMasterchainInfo",
            "params" => [],
        ]);

        try {
            return Hydrator::extract(MasterchainInfo::class, $response->result);
        } catch (MarshallingException $e) {
            throw new ClientException(
                "Unable to extract MasterchainInfo response: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getConsensusBlock(): ConsensusBlock
    {
        $response = $this->query([
            "method" => "getConsensusBlock",
            "params" => [],
        ]);

        try {
            return Hydrator::extract(ConsensusBlock::class, $response->result);
        } catch (MarshallingException $e) {
            throw new ClientException(
                "Unable to extract ConsensusBlock response: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function lookupBlock(int $workchain,
                                int $shard,
                                ?int $seqno = null,
                                ?int $lt = null,
                                ?int $unixtime = null): TonResponse
    {
        $params = [
            "workchain" => $workchain,
            "shard" => $shard,
        ];

        if (!is_null($seqno)) {
            $params["seqno"] = $seqno;
        }

        if (!is_null($lt)) {
            $params["lt"] = $lt;
        }

        if (!is_null($unixtime)) {
            $params["unixtime"] = $unixtime;
        }

        return $this
            ->query([
                "method" => "lookupBlock",
                "params" => $params,
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function shards(int $seqno): TonResponse
    {
        return $this
            ->query([
                "method" => "shards",
                "params" => [
                    "seqno" => $seqno,
                ],
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function getBlockTransactions(int $workchain,
                                         int $shard,
                                         int $seqno,
                                         ?string $rootHash = null,
                                         ?string $fileHash = null,
                                         ?int $afterLt = null,
                                         ?string $afterHash = null,
                                         ?int $count = null): TonResponse
    {
        $params = [
            "workchain" => $workchain,
            "shard" => $shard,
            "seqno" => $seqno,
        ];

        if (!is_null($rootHash)) {
            $params["root_hash"] = $rootHash;
        }

        if (!is_null($fileHash)) {
            $params["file_hash"] = $fileHash;
        }

        if (!is_null($afterLt)) {
            $params["after_lt"] = $afterLt;
        }

        if (!is_null($afterHash)) {
            $params["after_hash"] = $afterHash;
        }

        if (!is_null($count)) {
            $params["count"] = $count;
        }

        return $this
            ->query([
                "method" => "getBlockTransactions",
                "params" => $params,
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function getBlockHeader(int $workchain,
                                   int $shard,
                                   int $seqno,
                                   ?string $rootHash = null,
                                   ?string $fileHash = null): TonResponse
    {
        $params = [
            "workchain" => $workchain,
            "shard" => $shard,
            "seqno" => $seqno,
        ];

        if (!is_null($rootHash)) {
            $params["root_hash"] = $rootHash;
        }

        if (!is_null($fileHash)) {
            $params["file_hash"] = $fileHash;
        }

        return $this
            ->query([
                "method" => "getBlockHeader",
                "params" => $params,
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function tryLocateTx(Address $source, Address $destination, int $createdLt): TonResponse
    {
        return $this
            ->query([
                "method" => "tryLocateTx",
                "params" => [
                    "source" => (string)$source,
                    "destination" => (string)$destination,
                    "created_lt" => $createdLt,
                ],
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function tryLocateResultTx(Address $source, Address $destination, int $createdLt): TonResponse
    {
        return $this
            ->query([
                "method" => "tryLocateResultTx",
                "params" => [
                    "source" => (string)$source,
                    "destination" => (string)$destination,
                    "created_lt" => $createdLt,
                ],
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function tryLocateSourceTx(Address $source, Address $destination, int $createdLt): TonResponse
    {
        return $this
            ->query([
                "method" => "tryLocateSourceTx",
                "params" => [
                    "source" => (string)$source,
                    "destination" => (string)$destination,
                    "created_lt" => $createdLt,
                ],
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function getConfigParam(int $configId, ?int $seqno = null): TonResponse
    {
        $params = [
            "config_id" => $configId,
        ];

        if (!is_null($seqno)) {
            $params["seqno"] = $seqno;
        }

        return $this
            ->query([
                "method" => "getConfigParam",
                "params" => $params,
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function runGetMethod(Address|string $address, string $method, array $stack): TonResponse
    {
        return $this
            ->query([
                "method" => "runGetMethod",
                "params" => [
                    "address" => (string)$address,
                    "method" => $method,
                    "stack" => $stack,
                ],
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function sendBoc(Cell|string $boc): TonResponse
    {
        try {
            return $this
                ->query([
                    "method" => "sendBoc",
                    "params" => [
                        "boc" => is_string($boc) ? $boc : $boc->toBoc(),
                    ],
                ])
                ->asTonResponse();
        } catch (CellException $e) {
            throw new ClientException(
                "Boc serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function sendBocReturnHash(Cell|string $boc): TonResponse
    {
        try {
            return $this
                ->query([
                    "method" => "sendQuery",
                    "params" => [
                        "boc" => is_string($boc) ? $boc : $boc->toBoc(),
                    ],
                ])
                ->asTonResponse();
        } catch (CellException $e) {
            throw new ClientException(
                "Boc serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function sendQuery(array $body): TonResponse
    {
        return $this
            ->query([
                "method" => "sendQuery",
                "params" => $body,
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function estimateFee(array $body): TonResponse
    {
        return $this
            ->query([
                "method" => "estimateFee",
                "params" => $body,
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function jsonRPC(array $params): JsonRpcResponse
    {
        return $this->query($params);
    }

    /**
     * @throws ClientException
     * @throws ValidationException
     * @throws TimeoutException
     */
    private function query(array $params): JsonRpcResponse
    {
        $headers = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "User-Agent" => "olifanton-client/" . Version::LIBRARY_VERSION . " php/" . PHP_VERSION,
        ];

        if ($this->options->apiKey) {
            $headers["X-Api-Key"] = $this->options->apiKey;
        }

        try {
            $request = new Request(
                "POST",
                $this->options->baseUri . "/jsonRPC",
                $headers,
                json_encode($params, JSON_THROW_ON_ERROR),
            );
            $response = $this->httpClient->send($request);

            return $this->hydrateJsonRpcResponse($response->getBody()->getContents());
        } catch (\JsonException $e) {
            throw new ClientException(
                "JSON RPC body serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            [$errCode, $errMessage] = $this->tryExtractError($responseBody);

            if ($statusCode === 422) {
                throw new ValidationException(
                    ($errMessage) ?: "Validation error",
                    ($errCode) ?: $statusCode,
                    $e
                );
            } else if ($statusCode === 504) {
                throw new TimeoutException(
                    ($errMessage) ?: "Lite Server Timeout",
                    ($errCode) ?: $statusCode,
                    $e
                );
            }

            throw new ClientException(
                ($errMessage) ?: "Toncenter request error: " . $e->getMessage(),
                ($errCode) ?: $statusCode,
                $e
            );
        } catch (\Throwable $e) {
            throw new ClientException(
                "Toncenter request error: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws ClientException
     */
    private function hydrateJsonRpcResponse(string $responseBody): JsonRpcResponse
    {
        try {
            $body = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

            if (isset($body["ok"], $body["result"], $body["jsonrpc"])) {
                return new JsonRpcResponse(
                    ok: (bool)$body["ok"],
                    result: $body["result"],
                    error: isset($body["error"]) ? (string)$body["error"] : null,
                    code: isset($body["code"])? (int)$body["code"] : null,
                    jsonrpc: (string)$body["jsonrpc"],
                    id: isset($body["id"]) ? (string)$body["id"] : null,
                );
            }

            throw new ClientException("Invalid JSON RPC answer: `" . $responseBody . "`");
        } catch (\JsonException $e) {
            throw new ClientException(
                "JSON RPC answer body parsing error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    private function tryExtractError(string $responseBody): array
    {
        try {
            $body = json_decode($responseBody, true, 32, JSON_THROW_ON_ERROR);

            if (isset($body["error"], $body["code"])) {
                return [(int)$body["code"], $body["error"]];
            }
        } catch (\Throwable $e) {}

        return [null, null];
    }
}
