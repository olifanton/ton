<?php declare(strict_types=1);

namespace Olifanton\Ton\Transports\Toncenter;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\AddressState;
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
use Olifanton\Ton\Transports\Toncenter\Responses\Shards;
use Olifanton\Ton\Transports\Toncenter\Responses\Transaction;
use Olifanton\Ton\Transports\Toncenter\Responses\TransactionsList;
use Olifanton\Ton\Transports\Toncenter\Responses\UnrecognizedSmcRunResult;
use Olifanton\Ton\Transports\Toncenter\Responses\WalletInformation;
use Olifanton\TypedArrays\Uint8Array;

/**
 * Toncenter API client
 */
interface ToncenterV2Client
{
    /**
     * Get basic information about the address: balance, code, data, last_transaction_id.
     *
     * @link https://toncenter.com/api/v2/#/accounts/get_address_information_getAddressInformation_get
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
     * @link https://toncenter.com/api/v2/#/accounts/get_extended_address_information_getExtendedAddressInformation_get
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
     * @link https://toncenter.com/api/v2/#/accounts/get_wallet_information_getWalletInformation_get
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
     * @link https://toncenter.com/api/v2/#/accounts/get_transactions_getTransactions_get
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
     * @link https://toncenter.com/api/v2/#/accounts/get_address_balance_getAddressBalance_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getAddressBalance(Address $address): BigInteger;

    /**
     * Get state of a given address. State can be either `uninitialized`, `active` or `frozen`.
     *
     * @link https://toncenter.com/api/v2/#/accounts/get_address_getAddressState_get
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
     * @link https://toncenter.com/api/v2/#/accounts/pack_address_packAddress_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function packAddress(string $rawAddress): string;

    /**
     * Convert an address from human-readable to raw format.
     *
     * @link https://toncenter.com/api/v2/#/accounts/unpack_address_unpackAddress_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function unpackAddress(string $address): string;

    /**
     * Get all possible address forms.
     *
     * @link https://toncenter.com/api/v2/#/accounts/detect_address_detectAddress_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function detectAddress(Address | string $address): AddressDetectionResult;

    /**
     * Get up-to-date masterchain state.
     *
     * @link https://toncenter.com/api/v2/#/blocks/get_masterchain_info_getMasterchainInfo_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getMasterchainInfo(): MasterchainInfo;

    /**
     * Get consensus block and its update timestamp.
     *
     * @link https://toncenter.com/api/v2/#/blocks/get_consensus_block_getConsensusBlock_get
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
     * @link https://toncenter.com/api/v2/#/blocks/lookup_block_lookupBlock_get
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
     * @link https://toncenter.com/api/v2/#/blocks/shards_shards_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function shards(int $seqno): Shards;

    /**
     * Get transactions of the given block.
     *
     * @param int $workchain Workchain id
     * @param string $shard Shard id
     * @param int $seqno Block's height
     * @param string|null $rootHash
     * @param string|null $fileHash
     * @param int|null $afterLt
     * @param string|null $afterHash
     * @param int|null $count
     * @link https://toncenter.com/api/v2/#/blocks/get_block_transactions_getBlockTransactions_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getBlockTransactions(int $workchain,
                                         string $shard,
                                         int $seqno,
                                         ?string $rootHash = null,
                                         ?string $fileHash = null,
                                         ?int $afterLt = null,
                                         ?string $afterHash = null,
                                         ?int $count = null): BlockTransactions;


    /**
     * Get metadata of a given block.
     *
     * @param int $workchain  Workchain id
     * @param string $shard Shard id
     * @param int $seqno Block's height
     * @param string|null $rootHash
     * @param string|null $fileHash
     * @link https://toncenter.com/api/v2/#/blocks/get_block_header_getBlockHeader_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getBlockHeader(int $workchain,
                                   string $shard,
                                   int $seqno,
                                   ?string $rootHash = null,
                                   ?string $fileHash = null): BlockHeader;

    /**
     * Locate outcoming transaction of destination address by incoming message.
     *
     * @link https://toncenter.com/api/v2/#/transactions/get_try_locate_tx_tryLocateTx_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function tryLocateTx(Address $source, Address $destination, string $createdLt): Transaction;

    /**
     * Locate outcoming transaction of destination address by incoming message
     *
     * @link https://toncenter.com/api/v2/#/transactions/get_try_locate_result_tx_tryLocateResultTx_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function tryLocateResultTx(Address $source, Address $destination, string $createdLt): Transaction;

    /**
     * Locate incoming transaction of source address by outcoming message.
     *
     * @link https://toncenter.com/api/v2/#/transactions/get_try_locate_source_tx_tryLocateSourceTx_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function tryLocateSourceTx(Address $source, Address $destination, string $createdLt): Transaction;

    /**
     * Get config by id.
     *
     * @param int $configId Config id
     * @param int|null $seqno Masterchain seqno. If not specified, latest blockchain state will be used.
     * @link https://toncenter.com/api/v2/#/get%20config/get_config_param_getConfigParam_get
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function getConfigParam(int $configId, ?int $seqno = null): ConfigInfo;

    /**
     * Run get method on smart contract.
     *
     * @param Address $address Smart contract address
     * @param string $method Method name
     * @param string[][] $stack Stack array
     * @link https://toncenter.com/api/v2/#/run%20method/run_get_method_runGetMethod_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function runGetMethod(Address $address, string $method, array $stack = []): UnrecognizedSmcRunResult;

    /**
     * Send serialized boc file: fully packed and serialized external message to blockchain.
     *
     * @link https://toncenter.com/api/v2/#/send/send_boc_sendBoc_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendBoc(Cell | Uint8Array | string $boc): TonResponse;

    /**
     * Send serialized boc file: fully packed and serialized external message to blockchain. The method returns message hash.
     *
     * @link https://toncenter.com/api/v2/#/send/send_boc_return_hash_sendBocReturnHash_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendBocReturnHash(Cell | Uint8Array | string $boc): TonResponse;

    /**
     * Send query - unpacked external message.
     *
     * This method takes address, body and init-params (if any), packs it to external message and sends to network.
     * All params should be boc-serialized.
     *
     * @param array{addres: string, body: string, init_code?: string, init_data?: string} $body
     * @link https://toncenter.com/api/v2/#/send/send_query_sendQuery_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendQuery(array $body): TonResponse;

    /**
     * Estimate fees required for query processing. body, init-code and init-data accepted in serialized format (b64-encoded).
     *
     * @param Address|string $address Address in any format
     * @param Cell|Uint8Array|string $body b64-encoded cell with message body
     * @param Cell|Uint8Array|string|null $initCode b64-encoded cell with init-code
     * @param Cell|Uint8Array|string|null $initData b64-encoded cell with init-data
     * @param bool $ignoreChksig If true during test query processing assume that all chksig operations return True
     * @link https://toncenter.com/api/v2/#/send/estimate_fee_estimateFee_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function estimateFee(
        Address | string $address,
        Cell | Uint8Array | string $body,
        Cell | Uint8Array | string | null $initCode = null,
        Cell | Uint8Array | string | null $initData = null,
        bool $ignoreChksig = true,
    ): BigInteger;

    /**
     * All methods in the API are available through JSON-RPC protocol.
     *
     * @param array{method: string, params: array} $params
     * @return JsonRpcResponse
     * @link https://toncenter.com/api/v2/#/json%20rpc/jsonrpc_handler_jsonRPC_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function jsonRPC(array $params): JsonRpcResponse;
}
