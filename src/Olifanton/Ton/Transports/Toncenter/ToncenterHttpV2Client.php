<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter;

use Brick\Math\BigInteger;
use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\AddressState;
use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Olifanton\Ton\Transports\Toncenter\Exceptions\ClientException;
use Olifanton\Ton\Transports\Toncenter\Exceptions\TimeoutException;
use Olifanton\Ton\Transports\Toncenter\Exceptions\ValidationException;
use Olifanton\Ton\Transports\Toncenter\Models\JsonRpcResponse;
use Olifanton\Ton\Transports\Toncenter\Models\TonResponse;
use Olifanton\Ton\Transports\Toncenter\Responses\AddressDetectionResult;
use Olifanton\Ton\Transports\Toncenter\Responses\BlockHeader;
use Olifanton\Ton\Transports\Toncenter\Responses\BlockIdExt;
use Olifanton\Ton\Transports\Toncenter\Responses\BlockTransactions;
use Olifanton\Ton\Transports\Toncenter\Responses\ConfigInfo;
use Olifanton\Ton\Transports\Toncenter\Responses\ConsensusBlock;
use Olifanton\Ton\Transports\Toncenter\Responses\ExtendedFullAccountState;
use Olifanton\Ton\Transports\Toncenter\Responses\FullAccountState;
use Olifanton\Ton\Transports\Toncenter\Responses\MasterchainInfo;
use Olifanton\Ton\Transports\Toncenter\Responses\QueryFees;
use Olifanton\Ton\Transports\Toncenter\Responses\Shards;
use Olifanton\Ton\Transports\Toncenter\Responses\Transaction;
use Olifanton\Ton\Transports\Toncenter\Responses\TransactionsList;
use Olifanton\Ton\Transports\Toncenter\Responses\UnrecognizedSmcRunResult;
use Olifanton\Ton\Transports\Toncenter\Responses\WalletInformation;
use Olifanton\TypedArrays\Uint8Array;
use Psr\Http\Client\ClientExceptionInterface;

class ToncenterHttpV2Client implements ToncenterV2Client
{
    public function __construct(private readonly HttpMethodsClientInterface $httpClient,
                                private readonly ClientOptions $options,
    ) {}

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

        return $this->hydrateResponseModel(FullAccountState::class, $response->result);
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

        return $this->hydrateResponseModel(ExtendedFullAccountState::class, $response->result);
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

        return $this->hydrateResponseModel(WalletInformation::class, $response->result);
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

        return $this->hydrateResponseModel(TransactionsList::class, ["items" => $response->result]);
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

        return $this->hydrateResponseModel(AddressDetectionResult::class, $response->result);
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

        return $this->hydrateResponseModel(MasterchainInfo::class, $response->result);
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

        return $this->hydrateResponseModel(ConsensusBlock::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function lookupBlock(int $workchain,
                                string $shard,
                                ?int $seqno = null,
                                ?int $lt = null,
                                ?int $unixtime = null): BlockIdExt
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

        if (is_null($seqno) && is_null($lt) && is_null($unixtime)) {
            throw new \InvalidArgumentException("Seqno, LT or unixtime should be defined");
        }

        $response = $this->query([
            "method" => "lookupBlock",
            "params" => $params,
        ]);

        return $this->hydrateResponseModel(BlockIdExt::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function shards(int $seqno): Shards
    {
        $response = $this
            ->query([
                "method" => "shards",
                "params" => [
                    "seqno" => $seqno,
                ],
            ]);

        return $this->hydrateResponseModel(Shards::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function getBlockTransactions(int $workchain,
                                         string $shard,
                                         int $seqno,
                                         ?string $rootHash = null,
                                         ?string $fileHash = null,
                                         ?int $afterLt = null,
                                         ?string $afterHash = null,
                                         ?int $count = null): BlockTransactions
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

        $response = $this
            ->query([
                "method" => "getBlockTransactions",
                "params" => $params,
            ]);

        return $this->hydrateResponseModel(BlockTransactions::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function getBlockHeader(int $workchain,
                                   string $shard,
                                   int $seqno,
                                   ?string $rootHash = null,
                                   ?string $fileHash = null): BlockHeader
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

        $response = $this
            ->query([
                "method" => "getBlockHeader",
                "params" => $params,
            ]);

        return $this->hydrateResponseModel(BlockHeader::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function tryLocateTx(Address $source, Address $destination, string $createdLt): Transaction
    {
        $response = $this
            ->query([
                "method" => "tryLocateTx",
                "params" => [
                    "source" => (string)$source,
                    "destination" => (string)$destination,
                    "created_lt" => $createdLt,
                ],
            ]);

        return $this->hydrateResponseModel(Transaction::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function tryLocateResultTx(Address $source, Address $destination, string $createdLt): Transaction
    {
        $response = $this
            ->query([
                "method" => "tryLocateResultTx",
                "params" => [
                    "source" => (string)$source,
                    "destination" => (string)$destination,
                    "created_lt" => $createdLt,
                ],
            ]);

        return $this->hydrateResponseModel(Transaction::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function tryLocateSourceTx(Address $source, Address $destination, string $createdLt): Transaction
    {
        $response = $this
            ->query([
                "method" => "tryLocateSourceTx",
                "params" => [
                    "source" => (string)$source,
                    "destination" => (string)$destination,
                    "created_lt" => $createdLt,
                ],
            ]);

        return $this->hydrateResponseModel(Transaction::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function getConfigParam(int $configId, int|string|null $seqno = null): ConfigInfo
    {
        $params = [
            "config_id" => $configId,
        ];

        if (!is_null($seqno)) {
            $params["seqno"] = $seqno;
        }

        $response = $this
            ->query([
                "method" => "getConfigParam",
                "params" => $params,
            ]);

        return $this->hydrateResponseModel(ConfigInfo::class, $response->result["config"]);
    }

    /**
     * @inheritDoc
     */
    public function runGetMethod(Address|string $address, string $method, array $stack = []): UnrecognizedSmcRunResult
    {
        $response = $this
            ->query([
                "method" => "runGetMethod",
                "params" => [
                    "address" => (string)$address,
                    "method" => $method,
                    "stack" => $stack,
                ],
            ]);

        return $this->hydrateResponseModel(UnrecognizedSmcRunResult::class, $response->result);
    }

    /**
     * @inheritDoc
     */
    public function sendBoc(Cell | Uint8Array | string $boc): TonResponse
    {
        return $this
            ->query([
                "method" => "sendBoc",
                "params" => [
                    "boc" => $this->serializeBoc($boc),
                ],
            ])
            ->asTonResponse();
    }

    /**
     * @inheritDoc
     */
    public function sendBocReturnHash(Cell | Uint8Array | string $boc): TonResponse
    {
        return $this
            ->query([
                "method" => "sendBocReturnHash",
                "params" => [
                    "boc" => $this->serializeBoc($boc),
                ],
            ])
            ->asTonResponse();
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
    public function estimateFee(
        Address | string $address,
        Cell | Uint8Array | string $body,
        Cell | Uint8Array | string | null $initCode = null,
        Cell | Uint8Array | string | null $initData = null,
        bool $ignoreChksig = true,
    ): QueryFees
    {
        $params = [
            "address" => (string)$address,
            "body" => $this->serializeBoc($body),
            "ignore_chksig" => $ignoreChksig,
        ];

        if ($initCode !== null) {
            $params["init_code"] = $this->serializeBoc($initCode);
        }

        if ($initData !== null) {
            $params["init_data"] = $this->serializeBoc($initData);
        }

        $response = $this
            ->query([
                "method" => "estimateFee",
                "params" => $params,
            ]);

        return $this->hydrateResponseModel(QueryFees::class, $response->result);
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
     */
    private function serializeBoc(Cell | Uint8Array | string $boc): string
    {
        if (is_string($boc) && !preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $boc)) {
            throw new \InvalidArgumentException(
                "If a BoC string is passed, then it must be a base64-serialized string",
            );
        }

        if ($boc instanceof Cell) {
            try {
                $boc = $boc->toBoc(has_idx: false);
            } catch (CellException $e) {
                throw new ClientException(
                    "Boc serialization error: " . $e->getMessage(),
                    $e->getCode(),
                    $e,
                );
            }
        }

        if ($boc instanceof Uint8Array) {
            $boc = Bytes::bytesToBase64($boc);
        }

        if (!is_string($boc)) {
            throw new \RuntimeException("Unexpected BoC serialization error");
        }

        return $boc;
    }

    /**
     * @param array{method: string, params: array, jsonrpc?: string, id?: string} $params
     * @throws ClientException
     * @throws ValidationException
     * @throws TimeoutException
     */
    private function query(array $params): JsonRpcResponse
    {
        $headers = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
            "User-Agent" => "php-olifanton-client/php-" . PHP_VERSION,
        ];

        if ($this->options->apiKey) {
            $headers["X-Api-Key"] = $this->options->apiKey;
        }

        if (!isset($params["jsonrpc"])) {
            $params["jsonrpc"] = "2.0";
        }

        if (!isset($params["id"])) {
            $params["id"] = (string)hrtime(true);
        }

        try {
            $response = $this
                ->httpClient
                ->send(
                    "POST",
                    $this->options->baseUri . "/jsonRPC",
                    $headers,
                    json_encode($params, JSON_THROW_ON_ERROR),
                );

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                if ($this->options->requestDelay > 0) {
                    usleep((int)($this->options->requestDelay * 1000000));
                }

                return $this->hydrateJsonRpcResponse($response->getBody()->getContents());
            }

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            [$errCode, $errMessage] = $this->tryExtractError($responseBody);

            if ($statusCode === 422) {
                throw new ValidationException(
                    ($errMessage) ?: "Validation error",
                    ($errCode) ?: $statusCode,
                );
            } else if ($statusCode === 504) {
                throw new TimeoutException(
                    ($errMessage) ?: "Lite Server Timeout",
                    ($errCode) ?: $statusCode,
                );
            }

            throw new ClientException(
                ($errMessage) ?: "Toncenter request error: " . $response->getReasonPhrase(),
                ($errCode) ?: $statusCode,
            );
        } catch (\JsonException $e) {
            throw new ClientException(
                "JSON RPC body serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (ClientExceptionInterface $e) {
            throw new ClientException(
                "Toncenter client request error: " . $e->getMessage(),
                0,
                $e,
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

    /**
     * @template T
     * @param class-string<T> $responseClazz
     * @param mixed $data
     * @return T
     * @throws ClientException
     */
    private function hydrateResponseModel(string $responseClazz, mixed $data)
    {
        try {
            return Hydrator::extract($responseClazz, $data);
        // @codeCoverageIgnoreStart
        } catch (MarshallingException $e) {
            throw new ClientException(
                "Unable to extract $responseClazz response: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private function tryExtractError(string $responseBody): array
    {
        try {
            $body = json_decode($responseBody, true, 32, JSON_THROW_ON_ERROR);

            if (isset($body["error"], $body["code"])) {
                return [(int)$body["code"], $body["error"]];
            }
        } catch (\Throwable) {}

        return [null, null];
    }
}
