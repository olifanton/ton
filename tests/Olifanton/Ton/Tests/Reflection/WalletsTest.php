<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Reflection;

use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\Messages\StateInit;
use Olifanton\Ton\Helpers\KeyPair;
use Olifanton\Ton\Reflection\Wallets;
use PHPUnit\Framework\TestCase;
use Olifanton\Ton\Contracts\Wallets as WalletsSmc;

class WalletsTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testDetermineByStateInitAllWalletsSynthetic(): void
    {
        $cases = $this->getSyntheticCases(KeyPair::random());

        foreach ($cases as $smcClass => $options) {
            $this->assertEquals(
                $smcClass,
                Wallets::determineByStateInit($options["factory"]()),
                "Wallet: " . $smcClass,
            );
        }
    }

    /**
     * @throws \Throwable
     */
    public function testDetermineByStateInitConnect(): void
    {
        $s = trim(file_get_contents(STUB_DATA_DIR . "/connect/state-init.txt"));
        $stateInit = StateInit::fromBase64($s);
        $walletClass = Wallets::determineByStateInit($stateInit);
        $this->assertEquals(WalletsSmc\V4\WalletV4R2::class, $walletClass);
    }

    /**
     * @throws \Throwable
     */
    public function testExtractPublicDataSynthetic(): void
    {
        $kp = KeyPair::random();
        $cases = $this->getSyntheticCases($kp);

        foreach ($cases as $smcClass => $options) {
            $data = Wallets::extractPublicData($options["factory"]());
            $this->assertEquals(
                $kp->publicKey,
                $data->publicKey,
                "Wallet: " . $smcClass,
            );
        }
    }

    /**
     * @throws \Throwable
     */
    public function testExtractPublicDataByStateInitConnect(): void
    {
        $s = trim(file_get_contents(STUB_DATA_DIR . "/connect/state-init.txt"));
        $stateInit = StateInit::fromBase64($s);
        $data = Wallets::extractPublicData($stateInit);

        $this->assertEquals(
            "9918e493ad0ef35141ef851926c39d87616fea4724afa25e3255c21d3bded5b0",
            Bytes::bytesToHexString($data->publicKey),
        );
        $this->assertEquals(
            "0:5849a7639271b27721ec623a240c93aeefddefaf451d1a74b6b42b8a902cae15",
            $data->address->toString(false),
        );
    }

    /**
     * @return array<class-string<WalletsSmc\Wallet>, array{factory: callable}>
     */
    private function getSyntheticCases(\Olifanton\Interop\KeyPair $kp): array
    {
        return [
            WalletsSmc\Highload\HighloadWalletV2::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\Highload\HighloadWalletV2(
                        new WalletsSmc\Highload\HighloadV2Options(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],

            WalletsSmc\Simple\SimpleWalletR1::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\Simple\SimpleWalletR1(
                        new WalletsSmc\WalletOptions(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],
            WalletsSmc\Simple\SimpleWalletR2::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\Simple\SimpleWalletR2(
                        new WalletsSmc\WalletOptions(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],
            WalletsSmc\Simple\SimpleWalletR3::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\Simple\SimpleWalletR3(
                        new WalletsSmc\WalletOptions(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],

            WalletsSmc\V2\WalletV2R1::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\V2\WalletV2R1(
                        new WalletsSmc\WalletOptions(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],
            WalletsSmc\V2\WalletV2R2::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\V2\WalletV2R2(
                        new WalletsSmc\WalletOptions(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],

            WalletsSmc\V3\WalletV3R1::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\V3\WalletV3R1(
                        new WalletsSmc\V3\WalletV3Options(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],
            WalletsSmc\V3\WalletV3R2::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\V3\WalletV3R2(
                        new WalletsSmc\V3\WalletV3Options(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],

            WalletsSmc\V4\WalletV4R1::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\V4\WalletV4R1(
                        new WalletsSmc\V4\WalletV4Options(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],
            WalletsSmc\V4\WalletV4R2::class => [
                "factory" => static function () use ($kp): StateInit {
                    return (new WalletsSmc\V4\WalletV4R2(
                        new WalletsSmc\V4\WalletV4Options(
                            publicKey: $kp->publicKey,
                        ),
                    ))->getStateInit();
                },
            ],
        ];
    }
}
