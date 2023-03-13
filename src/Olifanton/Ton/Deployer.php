<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Brick\Math\BigInteger;
use Olifanton\Interop\Boc\Cell;
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
use Olifanton\Ton\Exceptions\DeployerException;
use Olifanton\Ton\Exceptions\TransportException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Deployer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly Transport $transport
    )
    {
    }

    /**
     * @throws DeployerException
     */
    public function deploy(DeployOptions $options, Deployable $deployable): void
    {
        $this
            ->logger
            ?->debug("External message construction for deploy " . $deployable::class);

        try {
            $deployer = $options->deployerWallet;
            $deployerAddress = $deployer->getAddress();
            $deployableAddress = $deployable->getAddress();

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

            $seqno = $deployer->seqno($this->transport);
            $sm = $deployer->createSigningMessage($seqno);
            $sm
                ->bits
                ->writeUint8(SendMode::PAY_GAS_SEPARATELY->value);
            $sm->refs[] = $internal->cell();

            $externalMessage = new ExternalMessage(
                new ExternalMessageOptions(
                    src: null,
                    dest: $deployerAddress,
                ),
                new MessageData(
                    $sm,
                )
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
                        $deployable->getAddress()->toString(true, isBounceable: false),
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
     * @throws DeployerException
     */
    public function estimateFee(Deployable $deployable): BigInteger
    {
        $stateInit = $deployable->getStateInit();
        $initCode = $stateInit->code;
        $initData = $stateInit->data;

        if (is_null($initCode)) {
            throw new DeployerException("Empty init code");
        }

        if (is_null($initData)) {
            throw new DeployerException("Empty init data");
        }

        try {
            return $this
                ->transport
                ->estimateFee(
                    $deployable->getAddress(),
                    Bytes::bytesToBase64((new Cell())->toBoc(has_idx: false)),
                    Bytes::bytesToBase64($initCode->toBoc(has_idx: false)),
                    Bytes::bytesToBase64($initData->toBoc(has_idx: false)),
                );
        } catch (CellException | TransportException $e) {
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
}
