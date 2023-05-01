<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Brick\Math\BigNumber;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Interfaces\Deployable;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ExternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\InternalMessage;
use Olifanton\Ton\Contracts\Messages\InternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\MessageData;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Exceptions\DeployerException;
use Olifanton\Ton\Exceptions\TransportException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Deployer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly Transport $transport,
    ) {}

    /**
     * @throws DeployerException
     */
    public function deploy(DeployOptions $options, Deployable $deployable): void
    {
        $this->validateStateInit($deployable);

        try {
            $this
                ->logger
                ?->debug("External message construction for deploy " . $deployable::class);
            $externalMessage = $this->createExternal(
                $options,
                $deployable,
                $options->deployerWallet->seqno($this->transport),
            );
        } catch (MessageException | ContractException | BitStringException $e) {
            $this
                ->logger
                ?->error(
                    sprintf("External message construction error: %s", $e->getMessage()),
                    [
                        "exception" => $e,
                        "deployable" => $deployable::class,
                    ]
                );

            throw new DeployerException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        try {
            $this
                ->transport
                ->sendMessage(
                    $externalMessage,
                    $options->deployerSecretKey,
                );
            $this
                ->logger
                ?->debug(
                    sprintf(
                        "Smart contract deployed to %s",
                        $deployable->getAddress()->toString(true, true, true),
                    ),
                );
        } catch (TransportException $e) {
            $this
                ->logger
                ?->error(
                    sprintf("External message sending error: %s", $e->getMessage()),
                    [
                        "exception" => $e,
                        "deployable" => $deployable::class,
                    ]
                );

            throw new DeployerException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Returns estimated deploy transaction fees.
     *
     * @throws DeployerException
     */
    public function estimateFee(DeployOptions $options, Deployable $deployable): BigNumber
    {
        // @TODO: It is probably worth rewriting for manual fees calculation

        $this->validateStateInit($deployable);

        try {
            return $this
                ->transport
                ->estimateFee(
                    $deployable->getAddress(),
                    Bytes::bytesToBase64(
                        $this
                            ->createExternal($options, $deployable, 0)
                            ->sign($options->deployerSecretKey)
                            ->toBoc(has_idx: false),
                    ),
                );
        } catch (CellException | TransportException | BitStringException | WalletException | MessageException | ContractException $e) {
            $this
                ->logger
                ?->error(
                    "Deploy fee calculation error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                        "deployable" => $deployable::class,
                    ]
                );

            throw new DeployerException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @throws BitStringException
     * @throws ContractException
     * @throws WalletException
     * @throws MessageException
     */
    private function createExternal(DeployOptions $options, Deployable $deployable, int $seqno): ExternalMessage
    {
        $deployableAddress = $deployable->getAddress();
        $deployerAddress = $options->deployerWallet->getAddress();
        $internal = new InternalMessage(
            new InternalMessageOptions(
                bounce: false,
                dest: $deployableAddress,
                value: $options->storageAmount,
                src: $deployerAddress,
            ),
            new MessageData(
                null,
                $deployable->getStateInit()->cell(),
            )
        );

        $sm = $options->deployerWallet->createSigningMessage($seqno);
        $sm
            ->bits
            ->writeUint8(SendMode::PAY_GAS_SEPARATELY->value);
        $sm->refs[] = $internal->cell();

        return new ExternalMessage(
            new ExternalMessageOptions(
                src: null,
                dest: $deployerAddress,
            ),
            new MessageData(
                $sm,
            )
        );
    }

    /**
     * @throws DeployerException
     */
    private function validateStateInit(Deployable $deployable)
    {
        $stateInit = $deployable->getStateInit();
        $initCode = $stateInit->code;
        $initData = $stateInit->data;

        // @codeCoverageIgnoreStart
        if (is_null($initCode)) {
            throw new DeployerException("Empty init code");
        }

        if (is_null($initData)) {
            throw new DeployerException("Empty init data");
        }
        // @codeCoverageIgnoreEnd
    }
}
