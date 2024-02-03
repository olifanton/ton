<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Nft;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Helpers\OffchainHelper;
use Olifanton\Ton\Transport;
use function Olifanton\Ton\Marshalling\Tvm\num;

class NftCollection extends AbstractContract
{
    private readonly NftCollectionOptions $options;

    public function __construct(NftCollectionOptions $options)
    {
        $this->options = $options;

        parent::__construct($options);
    }
    /**
     * @throws ContractException
     */
    public static function getDefaultCode(): Cell
    {
        return self::deserializeCode("b5ee9c724102140100021f000114ff00f4a413f4bcf2c80b0102016202030202cd04050201200e0f04e7d10638048adf000e8698180b8d848adf07d201800e98fe99ff6a2687d20699fea6a6a184108349e9ca829405d47141baf8280e8410854658056b84008646582a802e78b127d010a65b509e58fe59f80e78b64c0207d80701b28b9e382f970c892e000f18112e001718112e001f181181981e0024060708090201200a0b00603502d33f5313bbf2e1925313ba01fa00d43028103459f0068e1201a44343c85005cf1613cb3fccccccc9ed54925f05e200a6357003d4308e378040f4966fa5208e2906a4208100fabe93f2c18fde81019321a05325bbf2f402fa00d43022544b30f00623ba9302a402de04926c21e2b3e6303250444313c85005cf1613cb3fccccccc9ed54002c323401fa40304144c85005cf1613cb3fccccccc9ed54003c8e15d4d43010344130c85005cf1613cb3fccccccc9ed54e05f04840ff2f00201200c0d003d45af0047021f005778018c8cb0558cf165004fa0213cb6b12ccccc971fb008002d007232cffe0a33c5b25c083232c044fd003d0032c03260001b3e401d3232c084b281f2fff2742002012010110025bc82df6a2687d20699fea6a6a182de86a182c40043b8b5d31ed44d0fa40d33fd4d4d43010245f04d0d431d430d071c8cb0701cf16ccc980201201213002fb5dafda89a1f481a67fa9a9a860d883a1a61fa61ff480610002db4f47da89a1f481a67fa9a9a86028be09e008e003e00b01a500c6e");
    }

    public static function getName(): string
    {
        return "nft_collection";
    }

    /**
     * @throws ContractException
     */
    public static function createContentCell(string $collectionContentUrl, string $nftItemContentBaseUrl): Cell
    {
        try {
            $collectionContentCell = OffchainHelper::createUrlCell($collectionContentUrl);
            $commonContentCell = (new Builder())->writeBytes(Bytes::stringToBytes($nftItemContentBaseUrl))->cell();

            $contentCell = new Cell();
            $contentCell->refs[] = $collectionContentCell;
            $contentCell->refs[] = $commonContentCell;

            return $contentCell;
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     */
    public static function createRoyaltyCell(RoyaltyOptions $options): Cell
    {
        try {
            return (new Builder())
                ->writeUint((int)floor($options->royalty * $options->royaltyBase), 16)
                ->writeUint($options->royaltyBase, 16)
                ->writeAddress($options->royaltyAddress)
                ->cell();
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    protected function createCode(): Cell
    {
        return $this->options->code ?? self::getDefaultCode();
    }

    protected function createData(): Cell
    {
        try {
            $cell = (new Builder())
                ->writeAddress($this->options->ownerAddress)
                ->writeUint(0, 64)
                ->cell();
            $cell->refs[] = self::createContentCell(
                $this->options->collectionContentUrl,
                $this->options->nftItemContentBaseUrl,
            );
            $cell->refs[] = $this->options->nftItemCode;
            $cell->refs[] = self::createRoyaltyCell(new RoyaltyOptions(
                royalty: $this->options->royalty,
                royaltyBase: $this->options->royaltyBase,
                royaltyAddress: $this->options->royaltyAddress,
            ));

            return $cell;
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     */
    public static function createMintBody(MintOptions $options): Cell
    {
        try {
            $body = (new Builder())
                ->writeUint(1, 32)
                ->writeUint($options->queryId, 64)
                ->writeUint($options->itemIndex, 64)
                ->writeCoins($options->amount)
                ->cell();
            $nftItemContent = (new Builder())
                ->writeAddress($options->itemOwnerAddress)
                ->cell();
            $uriContent = (new Builder())
                ->writeBytes(Bytes::stringToBytes($options->itemContentUrl))
                ->cell();
            $nftItemContent->refs[] = $uriContent;
            $body->refs[] = $nftItemContent;

            return $body;
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     */
    public static function createGetRoyaltyParamsBody(int $queryId = 0): Cell
    {
        try {
            return (new Builder())
                ->writeUint(0x693d3950, 32)
                ->writeUint($queryId, 64)
                ->cell();
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     */
    public static function createChangeOwnerBody(Address $newOwnerAddress, int $queryId = 0): Cell
    {
        try {
            return (new Builder())
                ->writeUint(3, 32)
                ->writeUint($queryId, 64)
                ->writeAddress($newOwnerAddress)
                ->cell();
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     */
    public static function createEditContentBody(EditContentBodyOptions $options): Cell
    {
        try {
            $body = (new Builder())
                ->writeUint(4, 32)
                ->writeUint($options->queryId, 64)
                ->cell();
            $body->refs[] = self::createContentCell(
                $options->collectionContentUrl,
                $options->nftItemContentBaseUri,
            );
            $body->refs[] = self::createRoyaltyCell(new RoyaltyOptions(
                royalty: $options->royalty,
                royaltyBase: $options->royaltyBase,
                royaltyAddress: $options->royaltyAddress,
            ));

            return $body;
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
    public function getCollectionData(Transport $transport): NftCollectionData
    {
        $stack = $transport->runGetMethod($this, "get_collection_data");

        $itemsCount = $stack->currentBigInteger();
        $stack->next();
        $collectionContentCell = $stack->currentCell();
        $stack->next();
        $ownerAddressCell = $stack->currentCell();
        $collectionContentUrl = null;

        try {
            $collectionContentUrl = OffchainHelper::parseUrlCell($collectionContentCell);
        } catch (\InvalidArgumentException $_) {}

        try {
            return new NftCollectionData(
                $itemsCount->toInt(),
                $ownerAddressCell->beginParse()->loadAddress(),
                $collectionContentCell,
                $collectionContentUrl,
            );
        // @codeCoverageIgnoreStart
        } catch (CellException|SliceException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws TransportException
     * @throws ContractException
     */
    public function getNftItemAddress(Transport $transport, int $itemIndex): Address
    {
        try {
            $stack = $transport
                ->runGetMethod(
                    $this,
                    "get_nft_address_by_index",
                    [
                        num($itemIndex),
                    ]
                );

            return $stack->currentCell()->beginParse()->loadAddress();
        // @codeCoverageIgnoreStart
        } catch (SliceException|CellException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
}
