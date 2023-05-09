<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V4;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Messages\ExternalMessageOptions;
use Olifanton\Ton\Contracts\Messages\MessageData;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Transport;

class WalletV4R2 extends WalletV4
{
    public static function getName(): string
    {
        return "v4r2";
    }

    protected function createCode(): Cell
    {
        return self::deserializeCode("B5EE9C72410214010002D4000114FF00F4A413F4BCF2C80B010201200203020148040504F8F28308D71820D31FD31FD31F02F823BBF264ED44D0D31FD31FD3FFF404D15143BAF2A15151BAF2A205F901541064F910F2A3F80024A4C8CB1F5240CB1F5230CBFF5210F400C9ED54F80F01D30721C0009F6C519320D74A96D307D402FB00E830E021C001E30021C002E30001C0039130E30D03A4C8CB1F12CB1FCBFF1011121302E6D001D0D3032171B0925F04E022D749C120925F04E002D31F218210706C7567BD22821064737472BDB0925F05E003FA403020FA4401C8CA07CBFFC9D0ED44D0810140D721F404305C810108F40A6FA131B3925F07E005D33FC8258210706C7567BA923830E30D03821064737472BA925F06E30D06070201200809007801FA00F40430F8276F2230500AA121BEF2E0508210706C7567831EB17080185004CB0526CF1658FA0219F400CB6917CB1F5260CB3F20C98040FB0006008A5004810108F45930ED44D0810140D720C801CF16F400C9ED540172B08E23821064737472831EB17080185005CB055003CF1623FA0213CB6ACB1FCB3FC98040FB00925F03E20201200A0B0059BD242B6F6A2684080A06B90FA0218470D4080847A4937D29910CE6903E9FF9837812801B7810148987159F31840201580C0D0011B8C97ED44D0D70B1F8003DB29DFB513420405035C87D010C00B23281F2FFF274006040423D029BE84C600201200E0F0019ADCE76A26840206B90EB85FFC00019AF1DF6A26840106B90EB858FC0006ED207FA00D4D422F90005C8CA0715CBFFC9D077748018C8CB05CB0222CF165005FA0214CB6B12CCCCC973FB00C84014810108F451F2A7020070810108D718FA00D33FC8542047810108F451F2A782106E6F746570748018C8CB05CB025006CF165004FA0214CB6A12CB1FCB3FC973FB0002006C810108D718FA00D33F305224810108F459F2A782106473747270748018C8CB05CB025005CF165003FA0213CB6ACB1F12CB3FC973FB00000AF400C9ED54696225E5");
    }

    /**
     * @throws WalletException
     */
    public function createDeployAndInstallPluginMessage(PluginDeployOptions $options): ExternalMessage
    {
        try {
            $signingMessage = $this->createSigningMessage(
                $options->seqno,
                $options->expireAt,
                true,
            );
            $signingMessage
                ->bits
                ->writeUint(1, 8)
                ->writeInt($options->pluginWc, 8)
                ->writeCoins($options->pluginBalance);
            $signingMessage->refs[] = $options->pluginStateInit->cell();
            $signingMessage->refs[] = $options->pluginMsgBody;

            return new ExternalMessage(
                new ExternalMessageOptions(
                    src: null,
                    dest: $options->dstAddress,
                ),
                new MessageData(
                    body: $signingMessage,
                )
            );
        // @codeCoverageIgnoreStart
        } catch (BitStringException|MessageException $e) {
            throw new WalletException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws WalletException
     */
    public function createInstallPluginMessage(PlugOptions $options): ExternalMessage
    {
        return $this->createPluginMessage(true, $options);
    }

    /**
     * @throws WalletException
     */
    public function createUninstallPluginMessage(PlugOptions $options): ExternalMessage
    {
        return $this->createPluginMessage(false, $options);
    }

    /**
     * @throws \Olifanton\Ton\Exceptions\TransportException
     */
    public function isPluginInstalled(Transport $transport, Address $pluginAddress): bool
    {
        $result = $transport
            ->runGetMethod(
                $this,
                "is_plugin_installed",
                [
                    [
                        "num",
                        $pluginAddress->getWorkchain(),
                    ],
                    [
                        "num",
                        "0x" . Bytes::bytesToHexString($pluginAddress->getHashPart()),
                    ]
                ]
            );

        return !$result->currentBigInteger()->isZero();
    }

    /**
     * @return Address[]
     * @throws \Throwable
     */
    public function getInstalledPlugins(Transport $transport): array
    {
        $result = $transport
            ->runGetMethod(
                $this,
                "get_plugin_list",
            );

        return array_map(
            static fn($tuple): Address => new Address(
                $tuple[0]->toInt() . ":" . $tuple[1]->toBase(16),
            ),
            $result->currentTuple(),
        );
    }

    /**
     * @throws WalletException
     */
    private function createPluginMessage(bool $isInstall, PlugOptions $options): ExternalMessage
    {
        try {
            $signingMessage = $this->createSigningMessage(
                $options->seqno,
                $options->expireAt,
                true,
            );
            $signingMessage
                ->bits
                ->writeUint($isInstall ? 2 : 3, 8)
                ->writeInt($options->pluginAddress->getWorkchain(), 8)
                ->writeBytes($options->pluginAddress->getHashPart())
                ->writeCoins($options->amount)
                ->writeUint($options->queryId ?? 0, 64);

            return new ExternalMessage(
                new ExternalMessageOptions(
                    src: null,
                    dest: $this->getAddress(),
                ),
                new MessageData(
                    body: $signingMessage,
                )
            );
        // @codeCoverageIgnoreStart
        } catch (BitStringException|ContractException $e) {
            throw new WalletException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
}
