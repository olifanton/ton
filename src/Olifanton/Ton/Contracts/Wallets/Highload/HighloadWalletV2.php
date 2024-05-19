<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\Highload;

use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\DictSerializers;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Exceptions\HashmapException;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Interop\Boc\HashmapE;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ExternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\InternalMessage;
use Olifanton\Ton\Contracts\Messages\InternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\MessageData;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\Wallet;
use Olifanton\Ton\SendMode;

class HighloadWalletV2 extends AbstractWallet implements Wallet
{
    private HighloadV2Options $options;

    public function __construct(HighloadV2Options $contractOptions)
    {
        $this->options = $contractOptions;
        parent::__construct($contractOptions);
    }

    protected static function getHexCodeString(): string
    {
        return "b5ee9c724101090100e5000114ff00f4a413f4bcf2c80b010201200203020148040501eaf28308d71820d31fd33ff823aa1f5320b9f263ed44d0d31fd33fd3fff404d153608040f40e6fa131f2605173baf2a207f901541087f910f2a302f404d1f8007f8e16218010f4786fa5209802d307d43001fb009132e201b3e65b8325a1c840348040f4438ae63101c8cb1f13cb3fcbfff400c9ed54080004d03002012006070017bd9ce76a26869af98eb85ffc0041be5f976a268698f98e99fe9ff98fa0268a91040207a0737d098c92dbfc95dd1f140034208040f4966fa56c122094305303b9de2093333601926c21e2b39f9e545a";
    }

    public static function getName(): string
    {
        return "hlv2";
    }

    protected function createData(): Cell
    {
        try {
            return (new Builder())
                ->writeUint($this->options->subwalletId, 32)
                ->writeUint(0, 64)
                ->writeBytes($this->publicKey)
                ->writeDict(new HashmapE(16))
                ->cell();
        // @codeCoverageIgnoreStart
        } catch (BitStringException|HashmapException|CellException|SliceException $e) {
            throw new WalletException("Wallet data creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    public function createTransferMessage(array $transfers, ?TransferOptions $options = null): ExternalMessage
    {
        if (empty($transfers)) {
            throw new WalletException("At least one transfer is required");
        }

        if (count($transfers) > 254) {
            throw new WalletException("Sending no more than 254 transfers is possible");
        }

        $options = $options ?? new TransferOptions();

        try {
            $signingMessage = (new Builder())
                ->writeUint($this->options->subwalletId, 32)
                ->writeUint($this->generateQueryId($options->timeout), 64);
        } catch (BitStringException $e) {
            throw new WalletException($e->getMessage(), $e->getCode(), $e);
        }

        $dict = new HashmapE(16, DictSerializers::intKey(false));

        foreach (array_values($transfers) as $i => $transfer) {
            try {
                $body = is_string($transfer->payload)
                    ? $this->createTxtPayload($transfer->payload)
                    : $transfer->payload;
                $internalMessage = new InternalMessage(
                    new InternalMessageOptions(
                        bounce: $transfer->bounce,
                        dest: $transfer->dest,
                        value: $transfer->amount,
                        src: $this->getAddress(),
                    ),
                    new MessageData(
                        $body,
                        $transfer->stateInit?->cell(),
                    )
                );
                $dict->set(
                    $i,
                    (new Builder())
                        ->writeUint(
                            $transfer->sendMode instanceof SendMode
                                ? $transfer->sendMode->value
                                : $transfer->sendMode,
                            8,
                        )
                        ->writeRef($internalMessage->cell())
                        ->cell()
                );
            // @codeCoverageIgnoreStart
            } catch (BitStringException|MessageException|ContractException $e) {
                throw new WalletException(sprintf(
                    "Internal message %d serialization error: %s",
                    $i,
                    $e->getMessage()
                ), $e->getCode(), $e);
            }
            // @codeCoverageIgnoreEnd
        }

        try {
            $signingMessage->writeDict($dict);

            return new ExternalMessage(
                new ExternalMessageOptions(
                    null,
                    $this->getAddress(),
                ),
                new MessageData(
                    $signingMessage->cell(),
                    $options->seqno === 0 ? $this->getStateInit()->cell() : null,
                )
            );
        // @codeCoverageIgnoreStart
        } catch (MessageException|ContractException|CellException|HashmapException|SliceException $e) {
            throw new WalletException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private function generateQueryId(int $timeout): int
    {
        // Only for PHP x64
        return ((time() + $timeout) << 32) | rand(0, 2 ** 30);
    }
}
