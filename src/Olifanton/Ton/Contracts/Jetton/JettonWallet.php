<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Jetton;

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
use Olifanton\Ton\Transport;

class JettonWallet extends AbstractContract
{
    private JettonWalletOptions $options;

    public function __construct(?JettonWalletOptions $options = null)
    {
        $this->options = $options ?? new JettonWalletOptions();
        parent::__construct($this->options);
    }

    /**
     * @throws ContractException
     */
    public static function getDefaultCode(): Cell
    {
        return self::deserializeCode("b5ee9c7241021201000328000114ff00f4a413f4bcf2c80b0102016202030202cc0405001ba0f605da89a1f401f481f481a8610201d40607020148080900bb0831c02497c138007434c0c05c6c2544d7c0fc02f83e903e900c7e800c5c75c87e800c7e800c00b4c7e08403e29fa954882ea54c4d167c0238208405e3514654882ea58c511100fc02780d60841657c1ef2ea4d67c02b817c12103fcbc2000113e910c1c2ebcb853600201200a0b020120101101f500f4cffe803e90087c007b51343e803e903e90350c144da8548ab1c17cb8b04a30bffcb8b0950d109c150804d50500f214013e809633c58073c5b33248b232c044bd003d0032c032483e401c1d3232c0b281f2fff274013e903d010c7e801de0063232c1540233c59c3e8085f2dac4f3208405e351467232c7c6600c03f73b51343e803e903e90350c0234cffe80145468017e903e9014d6f1c1551cdb5c150804d50500f214013e809633c58073c5b33248b232c044bd003d0032c0327e401c1d3232c0b281f2fff274140371c1472c7cb8b0c2be80146a2860822625a020822625a004ad822860822625a028062849f8c3c975c2c070c008e00d0e0f009acb3f5007fa0222cf165006cf1625fa025003cf16c95005cc2391729171e25008a813a08208989680aa008208989680a0a014bcf2e2c504c98040fb001023c85004fa0258cf1601cf16ccc9ed5400705279a018a182107362d09cc8cb1f5230cb3f58fa025007cf165007cf16c9718018c8cb0524cf165006fa0215cb6a14ccc971fb0010241023000e10491038375f040076c200b08e218210d53276db708010c8cb055008cf165004fa0216cb6a12cb1f12cb3fc972fb0093356c21e203c85004fa0258cf1601cf16ccc9ed5400db3b51343e803e903e90350c01f4cffe803e900c145468549271c17cb8b049f0bffcb8b0a0822625a02a8005a805af3cb8b0e0841ef765f7b232c7c572cfd400fe8088b3c58073c5b25c60063232c14933c59c3e80b2dab33260103ec01004f214013e809633c58073c5b3327b55200083200835c87b51343e803e903e90350c0134c7e08405e3514654882ea0841ef765f784ee84ac7cb8b174cfcc7e800c04e81408f214013e809633c58073c5b3327b55205eccf23d");
    }

    protected function createCode(): Cell
    {
        return $this->options->code ?? self::getDefaultCode();
    }

    protected function createData(): Cell
    {
        return new Cell();
    }

    public static function getName(): string
    {
        return "ft_wallet";
    }

    /**
     * @throws ContractException
     */
    public function createTransferBody(TransferJettonOptions $options): Cell
    {
        try {
            $body = (new Builder())
                ->writeUint(0xf8a7ea5, 32)
                ->writeUint($options->queryId, 64)
                ->writeCoins($options->jettonAmount)
                ->writeAddress($options->toAddress)
                ->writeAddress($options->responseAddress)
                ->writeBit(false)
                ->writeCoins($options->forwardAmount ?? BigInteger::zero())
                ->writeBit(false);

            if ($options->forwardPayload) {
                $body->writeBytes($options->forwardPayload);
            }

            return $body->cell();
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws ContractException
     */
    public function createBurnBody(BurnOptions $options): Cell
    {
        try {
            return (new Builder())
                ->writeUint(0x595f07bc, 32)
                ->writeUint($options->queryId, 64)
                ->writeCoins($options->jettonAmount)
                ->writeAddress($options->responseAddress)
                ->cell();
        } catch (BitStringException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws ContractException
     * @throws TransportException
     */
    public function getWalletData(Transport $transport): JettonWalletData
    {
        try {
            $stack = $transport
                ->runGetMethod(
                    $this,
                    "get_wallet_data",
                );

            $balance = $stack->currentBigInteger();
            $stack->next();

            $ownerAddress = AddressHelper::parseAddressSlice($stack->currentCell()->beginParse());
            $stack->next();

            $minterAddress = AddressHelper::parseAddressSlice($stack->currentCell()->beginParse());
            $stack->next();

            $walletCode = $stack->currentCell();

            return new JettonWalletData(
                balance: $balance,
                ownerAddress: $ownerAddress,
                minterAddress: $minterAddress,
                walletCode: $walletCode,
            );
        } catch (SliceException|CellException $e) {
            throw new ContractException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
