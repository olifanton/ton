<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Brick\Math\BigInteger;
use Olifanton\Boc\Cell;
use Olifanton\Ton\Models\AddressState;
use Olifanton\Ton\Models\JsonRpcResponse;
use Olifanton\Ton\Models\TonResponse;
use Olifanton\Ton\Toncenter\Exceptions\ClientException;
use Olifanton\Ton\Toncenter\Exceptions\TimeoutException;
use Olifanton\Ton\Toncenter\Exceptions\ValidationException;
use Olifanton\Ton\Toncenter\Responses\AddressDetectionResult;
use Olifanton\Ton\Toncenter\Responses\BlockIdExt;
use Olifanton\Ton\Toncenter\Responses\ConsensusBlock;
use Olifanton\Ton\Toncenter\Responses\ExtendedFullAccountState;
use Olifanton\Ton\Toncenter\Responses\FullAccountState;
use Olifanton\Ton\Toncenter\Responses\MasterchainInfo;
use Olifanton\Ton\Toncenter\Responses\TransactionsList;
use Olifanton\Ton\Toncenter\Responses\WalletInformation;
use Olifanton\Utils\Address;

/**
 * Toncenter API client
 */
interface ToncenterClient
{
    /**
     * Get basic information about the address: balance, code, data, last_transaction_id.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getAddressInformation(Address $address): FullAccountState;

    /**
     * Similar to previous one but tries to parse additional information for known contract types.
     *
     * This method is based on tonlib's function getAccountState.
     * For detecting wallets we recommend to use getWalletInformation.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getExtendedAddressInformation(Address $address): ExtendedFullAccountState;

    /**
     * Retrieve wallet information.
     *
     * This method parses contract state and currently supports more wallet types than getExtendedAddressInformation: simple wallet, standart wallet, v3 wallet, v4 wallet.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getWalletInformation(Address $address): WalletInformation;

    /**
     * Get transaction history of a given address.
     *
     * @param Address $address Identifier of target TON account
     * @param int|null $limit Maximum number of transactions in response.
     * @param int|null $lt Logical time of transaction to start with, must be sent with `hash`.
     * @param string|null $hash Hash of transaction to start with, in base64 or hex encoding , must be sent with lt.
     * @param int|null $toLt Logical time of transaction to finish with (to get tx from lt to to_lt).
     * @param bool|null $archival By default, `getTransaction` request is processed by any available liteserver. If archival=true only liteservers with full history are used.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getTransactions(Address $address,
                                    ?int $limit = null,
                                    ?int $lt = null,
                                    ?string $hash = null,
                                    ?int $toLt = null,
                                    ?bool $archival = null): TransactionsList;

    /**
     * Get balance (in nanotons) of a given address.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getAddressBalance(Address $address): BigInteger;

    /**
     * Get state of a given address. State can be either `uninitialized`, `active` or `frozen`.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getAddressState(Address $address): AddressState;

    /**
     * Convert an address from raw to human-readable format.
     *
     * Raw address example: "0:83DFD552E63729B472FCBCC8C45EBCC6691702558B68EC7527E1BA403A0F31A8"
     *
     * @param string $rawAddress Identifier of target TON account in raw form.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function packAddress(string $rawAddress): string;

    /**
     * Convert an address from human-readable to raw format.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function unpackAddress(string $address): string;

    /**
     * Get all possible address forms.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function detectAddress(Address | string $address): AddressDetectionResult;

    /**
     * Get up-to-date masterchain state.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getMasterchainInfo(): MasterchainInfo;

    /**
     * Get consensus block and its update timestamp.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getConsensusBlock(): ConsensusBlock;

    /**
     * Look up block by either seqno, lt or unixtime.
     *
     * @param int $workchain Workchain id to look up block in
     * @param string $shard Shard id to look up block in
     * @param int|null $seqno Block's height
     * @param int|null $lt Block's logical time
     * @param int|null $unixtime Block's unixtime
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function lookupBlock(int $workchain,
                                string $shard,
                                ?int $seqno = null,
                                ?int $lt = null,
                                ?int $unixtime = null): BlockIdExt;

    /**
     * Get shards information.
     *
     * @param int $seqno Masterchain seqno to fetch shards of.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function shards(int $seqno): TonResponse;

    /**
     * Get transactions of the given block.
     *
     * @param int $workchain Workchain id
     * @param int $shard Shard id
     * @param int $seqno Block's height
     * @param string|null $rootHash
     * @param string|null $fileHash
     * @param int|null $afterLt
     * @param string|null $afterHash
     * @param int|null $count
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getBlockTransactions(int $workchain,
                                         int $shard,
                                         int $seqno,
                                         ?string $rootHash = null,
                                         ?string $fileHash = null,
                                         ?int $afterLt = null,
                                         ?string $afterHash = null,
                                         ?int $count = null): TonResponse;


    /**
     * Get metadata of a given block.
     *
     * @param int $workchain  Workchain id
     * @param int $shard Shard id
     * @param int $seqno Block's height
     * @param string|null $rootHash
     * @param string|null $fileHash
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getBlockHeader(int $workchain,
                                   int $shard,
                                   int $seqno,
                                   ?string $rootHash = null,
                                   ?string $fileHash = null): TonResponse;

    /**
     * Locate outcoming transaction of destination address by incoming message.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function tryLocateTx(Address $source, Address $destination, int $createdLt): TonResponse;

    /**
     * Locate outcoming transaction of destination address by incoming message
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function tryLocateResultTx(Address $source, Address $destination, int $createdLt): TonResponse;

    /**
     * Locate incoming transaction of source address by outcoming message.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function tryLocateSourceTx(Address $source, Address $destination, int $createdLt): TonResponse;

    /**
     * Get config by id.
     *
     * @param int $configId Config id
     * @param int|null $seqno Masterchain seqno. If not specified, latest blockchain state will be used.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getConfigParam(int $configId, ?int $seqno = null): TonResponse;

    /**
     * Run get method on smart contract.
     *
     * @param Address $address Smart contract address
     * @param string $method Method name
     * @param string[] $stack Stack array
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function runGetMethod(Address $address, string $method, array $stack): TonResponse;

    /**
     * Send serialized boc file: fully packed and serialized external message to blockchain.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendBoc(Cell | string $boc): TonResponse;

    /**
     * Send serialized boc file: fully packed and serialized external message to blockchain. The method returns message hash.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendBocReturnHash(Cell | string $boc): TonResponse;

    /**
     * Send query - unpacked external message.
     *
     * This method takes address, body and init-params (if any), packs it to external message and sends to network.
     * All params should be boc-serialized.
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendQuery(array $body): TonResponse;

    /**
     * Estimate fees required for query processing. body, init-code and init-data accepted in serialized format (b64-encoded).
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function estimateFee(array $body): TonResponse;

    /**
     * @param array $params
     * @return JsonRpcResponse
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function jsonRPC(array $params): JsonRpcResponse;
}
