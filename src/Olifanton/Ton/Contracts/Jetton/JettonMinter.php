<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Builder;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Exceptions\TransportException;
use Olifanton\Ton\Helpers\OffchainHelper;
use Olifanton\Ton\Transport;
use function Olifanton\Ton\Marshalling\Tvm\slice;

class JettonMinter extends AbstractContract
{
    private readonly JettonMinterOptions $options;

    public function __construct(JettonMinterOptions $contractOptions)
    {
        $this->options = $contractOptions;
        parent::__construct($contractOptions);
    }

    /**
     * @throws ContractException
     * @throws TransportException
     */
    public static function fromAddress(Transport $transport, Address $address): self
    {
        $data = self::getJettonDataInner($transport, $address);

        return new self(
            new JettonMinterOptions(
                adminAddress: $data->adminAddress,
                jettonContentUrl: $data->jettonContentUrl,
                jettonWalletCode: $data->jettonWalletCode,
                address: $address,
            ),
        );
    }

    protected function createCode(): Cell
    {
        return $this->options->code ?? self::deserializeCode("b5ee9c7241020b010001ed000114ff00f4a413f4bcf2c80b0102016202030202cc040502037a60090a03efd9910e38048adf068698180b8d848adf07d201800e98fe99ff6a2687d007d206a6a18400aa9385d47181a9aa8aae382f9702480fd207d006a18106840306b90fd001812881a28217804502a906428027d012c678b666664f6aa7041083deecbef29385d71811a92e001f1811802600271812f82c207f97840607080093dfc142201b82a1009aa0a01e428027d012c678b00e78b666491646580897a007a00658064907c80383a6465816503e5ffe4e83bc00c646582ac678b28027d0109e5b589666664b8fd80400fe3603fa00fa40f82854120870542013541403c85004fa0258cf1601cf16ccc922c8cb0112f400f400cb00c9f9007074c8cb02ca07cbffc9d05008c705f2e04a12a1035024c85004fa0258cf16ccccc9ed5401fa403020d70b01c3008e1f8210d53276db708010c8cb055003cf1622fa0212cb6acb1fcb3fc98042fb00915be200303515c705f2e049fa403059c85004fa0258cf16ccccc9ed54002e5143c705f2e049d43001c85004fa0258cf16ccccc9ed54007dadbcf6a2687d007d206a6a183618fc1400b82a1009aa0a01e428027d012c678b00e78b666491646580897a007a00658064fc80383a6465816503e5ffe4e840001faf16f6a2687d007d206a6a183faa904051007f09");
    }

    protected function createData(): Cell
    {
        try {
            $data = (new Builder())
                ->writeCoins(0)
                ->writeAddress($this->options->adminAddress)
                ->cell();
            $data->refs[] = OffchainHelper::createUrlCell($this->options->jettonContentUrl);
            $data->refs[] = $this->options->jettonWalletCode;

            return $data;
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    public static function getName(): string
    {
        return "ft_minter";
    }

    public function getAddress(): Address
    {
        return $this->options->address ?? parent::getAddress();
    }

    /**
     * @throws ContractException
     */
    public static function createMintBody(MintOptions $options): Cell
    {
        try {
            $body = (new Builder())
                ->writeUint(21, 32)
                ->writeUint($options->queryId, 64)
                ->writeAddress($options->destination)
                ->writeCoins($options->amount)
                ->cell();

            $transferBody = (new Builder())
                ->writeUint(0x178d4519, 32)
                ->writeUint($options->queryId, 64)
                ->writeCoins($options->jettonAmount)
                ->writeAddress(Address::NONE)
                ->writeAddress(Address::NONE)
                ->writeCoins(BigInteger::zero())
                ->writeBit(false)
                ->cell();

            $body->refs[] = $transferBody;

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
    public static function createChangeAdminBody(?Address $newAdminAddress, int $queryId = 0): Cell
    {
        try {
            return (new Builder())
                ->writeUint(3, 32)
                ->writeUint($queryId, 64)
                ->writeAddress($newAdminAddress)
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
    public static function createEditContentBody(string $jettonContentUrl, int $queryId = 0): Cell
    {
        try {
            $body = (new Builder())
                ->writeUint(4, 32)
                ->writeUint($queryId, 64)
                ->cell();
            $body->refs[] = OffchainHelper::createUrlCell($jettonContentUrl);

            return $body;
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     * @throws TransportException
     */
    public function getJettonData(Transport $transport): JettonData
    {
        return self::getJettonDataInner($transport, $this->getAddress());
    }

    /**
     * @throws ContractException
     * @throws TransportException
     */
    public function getJettonWalletAddress(Transport $transport, Address $ownerAddress): ?Address
    {
        try {
            $stack = $transport
                ->runGetMethod(
                    $this,
                    "get_wallet_address",
                    [
                        slice(
                            (new Builder())
                                ->writeAddress($ownerAddress)
                                ->cell()
                                ->beginParse()
                        ),
                    ]
                );
            $cell = $stack->currentCell();

            if (!$cell) {
                throw new ContractException("Getter response stack parsing error, empty cell");
            }

            return $cell->beginParse()->loadAddress();
        // @codeCoverageIgnoreStart
        } catch (BitStringException|CellException|SliceException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     * @throws TransportException
     */
    private static function getJettonDataInner(Transport $transport, Address $address): JettonData
    {
        try {
            $stack = $transport
                ->runGetMethod(
                    $address,
                    "get_jetton_data",
                );

            $totalSupply = $stack->currentBigInteger();
            $stack->next();

            $isMutable = $stack->currentBigInteger()->isNegative();
            $stack->next();

            $adminAddress = $stack->currentCell()->beginParse()->loadAddress();
            $stack->next();

            $jettonContentUrl = null;
            $jettonContentCell = $stack->currentCell();
            $stack->next();

            try {
                $jettonContentUrl = OffchainHelper::parseUrlCell($jettonContentCell);
            } catch (\InvalidArgumentException $_) {}

            $jettonWalletCode = $stack->currentCell();

            return new JettonData(
                totalSupply: $totalSupply,
                isMutable: $isMutable,
                adminAddress: $adminAddress,
                jettonContentUrl: $jettonContentUrl,
                jettonContentCell: $jettonContentCell,
                jettonWalletCode: $jettonWalletCode,
            );
        // @codeCoverageIgnoreStart
        } catch (SliceException|CellException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
}
