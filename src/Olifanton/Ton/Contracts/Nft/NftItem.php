<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Brick\Math\BigInteger;
use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Helpers\AddressHelper;
use Olifanton\Ton\Helpers\OffchainHelper;
use Olifanton\Ton\Transport;

class NftItem extends AbstractContract
{
    private readonly NftItemOptions $options;

    public function __construct(NftItemOptions $options)
    {
        $this->options = $options;
        parent::__construct($options);
    }

    /**
     * @throws ContractException
     */
    public static function getDefaultCode(): Cell
    {
        return self::deserializeCode("b5ee9c7241020d010001d0000114ff00f4a413f4bcf2c80b0102016202030202ce04050009a11f9fe00502012006070201200b0c02d70c8871c02497c0f83434c0c05c6c2497c0f83e903e900c7e800c5c75c87e800c7e800c3c00812ce3850c1b088d148cb1c17cb865407e90350c0408fc00f801b4c7f4cfe08417f30f45148c2ea3a1cc840dd78c9004f80c0d0d0d4d60840bf2c9a884aeb8c097c12103fcbc20080900113e910c1c2ebcb8536001f65135c705f2e191fa4021f001fa40d20031fa00820afaf0801ba121945315a0a1de22d70b01c300209206a19136e220c2fff2e192218e3e821005138d91c85009cf16500bcf16712449145446a0708010c8cb055007cf165005fa0215cb6a12cb1fcb3f226eb39458cf17019132e201c901fb00104794102a375be20a00727082108b77173505c8cbff5004cf1610248040708010c8cb055007cf165005fa0215cb6a12cb1fcb3f226eb39458cf17019132e201c901fb000082028e3526f0018210d53276db103744006d71708010c8cb055007cf165005fa0215cb6a12cb1fcb3f226eb39458cf17019132e201c901fb0093303234e25502f003003b3b513434cffe900835d27080269fc07e90350c04090408f80c1c165b5b60001d00f232cfd633c58073c5b3327b5520bf75041b");
    }

    protected function createCode(): Cell
    {
        return $this->options->code ?? self::getDefaultCode();
    }

    protected function createData(): Cell
    {
        try {
            return (new Builder())
                ->writeUint($this->options->index, 64)
                ->writeAddress($this->options->collectionAddress)
                ->cell();
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    public static function getName(): string
    {
        return "nft_item";
    }

    /**
     * @throws ContractException
     */
    public static function createTransferBody(NftTransferOptions $options): Cell
    {
        try {
            $bodyBuilder = (new Builder())
                ->writeUint(0x5fcc3d14, 32)
                ->writeUint($options->queryId, 64)
                ->writeAddress($options->newOwnerAddress)
                ->writeAddress($options->responseAddress)
                ->writeBit(false)
                ->writeCoins($options->forwardAmount ?? BigInteger::zero())
                ->writeBit(false);

            if ($options->forwardPayload) {
                $bodyBuilder->writeBytes($options->forwardPayload);
            }

            return $bodyBuilder->cell();
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     */
    public static function getStaticDataBody(int $queryId = 0): Cell
    {
        try {
            return (new Builder())
                ->writeUint(0x2fcb26a2, 32)
                ->writeUint($queryId, 64)
                ->cell();
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws TransportException
     * @throws ContractException
     */
    public function getItemData(Transport $transport): NftItemData
    {
        try {
            $stack = $transport->runGetMethod($this, "get_nft_data");

            $isInitialized = $stack->currentBigInteger()->toInt() === -1;
            $stack->next();

            $index = $stack->currentBigInteger()->toInt();
            $stack->next();

            $collectionAddress = null;

            try {
                $collectionAddress = AddressHelper::parseAddressSlice($stack->currentCell()->beginParse());
                $stack->next();
            } catch (\InvalidArgumentException $_) {}

            $ownerAddressCell = $stack->currentCell();
            $stack->next();
            $ownerAddress = $isInitialized ? AddressHelper::parseAddressSlice($ownerAddressCell->beginParse()) : null;

            $contentCell = $stack->currentCell();
            $contentUrl = null;

            if ($isInitialized && $collectionAddress === null) {
                try {
                    $contentUrl = OffchainHelper::parseUrlCell($contentCell);
                } catch (\InvalidArgumentException $_) {}
            }

            return new NftItemData(
                isInitialized: $isInitialized,
                index: $index,
                collectionAddress: $collectionAddress,
                contentCell: $contentCell,
                contentUrl: $contentUrl,
                ownerAddress: $ownerAddress,
            );
        // @codeCoverageIgnoreStart
        } catch (SliceException|CellException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
}
