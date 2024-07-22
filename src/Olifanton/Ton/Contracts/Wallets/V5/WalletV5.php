<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V5;

use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Slice;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ExternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\InternalMessage;
use Olifanton\Ton\Contracts\Messages\InternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\MessageData;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\Transfer;
use Olifanton\Ton\Contracts\Wallets\TransferOptions;
use Olifanton\Ton\Contracts\Wallets\Wallet;
use Olifanton\Ton\Contracts\Wallets\WalletId;

abstract class WalletV5 extends AbstractWallet implements Wallet
{
    protected WalletId $walletId;

    public const WALLET_VERSIONS_MAP = [
        "v5" => 0,
    ];

    public function __construct(protected readonly WalletV5Options $options)
    {
        parent::__construct($this->options);
        $this->walletId = $this->options->walletId ?? new WalletId(
            workchain: $this->options->workchain,
        );

        if (!isset(self::WALLET_VERSIONS_MAP[$this->walletId->walletVersion])) {
            throw new \InvalidArgumentException("Unknown version: " . $this->walletId->walletVersion);
        }
    }

    protected function createData(): Cell
    {
        try {
            $b = (new Builder())
                ->writeInt(0, 33); // seqno
            $this->writeWalletId($b);
            $b
                ->writeBytes($this->publicKey)
                ->writeBit(0); // Empty plugins dict

            return $b->cell();
            // @codeCoverageIgnoreStart
        } catch (BitStringException | CellException $e) {
            throw new WalletException("Wallet data creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws BitStringException
     * @throws CellException
     */
    protected function writeWalletId(Builder $builder): void
    {
        $builder->writeCell(
            (new Builder())
                ->writeInt($this->walletId->networkId->value, 32)
                ->writeInt($this->walletId->workchain ?? $this->options->workchain, 8)
                ->writeUint(self::WALLET_VERSIONS_MAP[$this->walletId->walletVersion], 8)
                ->writeUint($this->walletId->subwalletId, 32)
                ->cell(),
        );
    }

    public function createTransferMessage(array $transfers, TransferOptions|WalletV5TransferOptions|null $options = null): ExternalMessage
    {
        if (empty($transfers)) {
            throw new WalletException("At least one transfer is required");
        }

        if (count($transfers) > 255) {
            throw new WalletException("Sending no more than 255 transfers is possible");
        }

        $options = $options ?? new WalletV5TransferOptions();
        $seqno = $options->seqno;

        if ($seqno === null) {
            throw new WalletException("Seqno is required");
        }

        try {
            $signingMessageB = (new Builder());
            $signingMessageB->writeUint(0x7369676E, 32);
            $this->writeWalletId($signingMessageB);

            if ($seqno === 0) {
                for ($i = 0; $i < 32; $i++) {
                    $signingMessageB->writeBit(1);
                }
            } else {
                $expireAt = time() + $options->timeout;
                $signingMessageB->writeUint($expireAt, 32);
            }

            $signingMessageB
                ->writeUint($seqno, 32)
                ->writeUint(0, 1)
                ->writeRef(
                    (new Builder())->writeSlice($this->createTransfersCell($transfers))->cell(),
                );
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            throw new WalletException(
                "Signing message construction error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd

        $signingMessage = $signingMessageB->cell();

        try {
            // @phpstan-ignore-next-line
            return (new ExternalMessage(
                new ExternalMessageOptions(
                    src: null,
                    dest: $this->getAddress(),
                ),
                new MessageData(
                    $signingMessage,
                    $seqno === 0 ? $this->getStateInit()->cell() : null,
                ),
            ))->tailSigned(true);
            // @codeCoverageIgnoreStart
        } catch (MessageException|ContractException $e) {
            throw new WalletException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    public function createSigningMessage(int $seqno): Cell
    {
        throw new \DomainException("Not applicable");
    }

    /**
     * @param Transfer[] $transfers
     * @return Slice
     * @throws BitStringException
     * @throws CellException
     * @throws ContractException
     * @throws MessageException
     */
    protected function createTransfersCell(array $transfers): Slice
    {
        return array_reduce($transfers, function (Cell $cell, Transfer $transfer) {
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

            return (new Builder())
                ->writeRef($cell)
                ->writeUint(0x0ec3c86d, 32)
                ->writeUint(is_int($transfer->sendMode) ? $transfer->sendMode : $transfer->sendMode->value, 8)
                ->writeRef($internalMessage->cell())
                ->cell();
        }, new Cell())->beginParse();
    }
}
