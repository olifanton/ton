<?php declare(strict_types=1);

namespace Olifanton\Ton\Reflection;

use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\Messages\StateInit;
use Olifanton\Ton\Contracts\Wallets as WalletsSmc;
use Olifanton\Ton\Network;
use Olifanton\Ton\Reflection\Exceptions\WalletReflectionException;
use Olifanton\Ton\Reflection\Models\AddressPubkeyPair;

class Wallets
{
    private static ?array $HASHES = null;

    /**
     * @return class-string<WalletsSmc\Wallet>|null
     * @throws WalletReflectionException
     */
    public static function determineByStateInit(StateInit $stateInit): ?string
    {
        if (!$stateInit->code) {
            return null;
        }

        try {
            $codeHash = strtolower(Bytes::bytesToHexString($stateInit->code->hash()));
        } catch (CellException $e) {
            throw new WalletReflectionException("Code hash calculation error", $e->getCode(), $e);
        }

        foreach (static::ensureHashes() as $smcClass => $hash) {
            if ($hash === $codeHash) {
                return $smcClass;
            }
        }

        return null;
    }

    /**
     * @throws WalletReflectionException
     */
    public static function extractPublicData(StateInit $stateInit): AddressPubkeyPair
    {
        $walletClass = self::determineByStateInit($stateInit);

        if (!$walletClass) {
            throw new WalletReflectionException("Unknown StateInit");
        }

        if (!$stateInit->data) {
            throw new WalletReflectionException("Empty data");
        }

        try {
            $slice = $stateInit->data->beginParse();

            switch ($walletClass) {
                case WalletsSmc\Highload\HighloadWalletV2::class:
                    $walletId = $slice->loadUint(32)->toInt();
                    $slice->skipBits(64);
                    $publicKey = $slice->loadBits(256);
                    $instance = new WalletsSmc\Highload\HighloadWalletV2(
                        new WalletsSmc\Highload\HighloadV2Options(
                            publicKey: $publicKey,
                            subwalletId: $walletId,
                        ),
                    );
                    break;

                case WalletsSmc\Simple\SimpleWalletR1::class:
                case WalletsSmc\Simple\SimpleWalletR2::class:
                case WalletsSmc\Simple\SimpleWalletR3::class:
                case WalletsSmc\V2\WalletV2R1::class:
                case WalletsSmc\V2\WalletV2R2::class:
                    $slice->skipBits(32); // seqno
                    $publicKey = $slice->loadBits(256);
                    $instance = new $walletClass(new WalletsSmc\WalletOptions(
                        publicKey: $publicKey,
                    ));
                    break;

                case WalletsSmc\V3\WalletV3R1::class:
                case WalletsSmc\V3\WalletV3R2::class:
                case WalletsSmc\V4\WalletV4R1::class:
                case WalletsSmc\V4\WalletV4R2::class:
                    $slice->skipBits(32); // seqno
                    $walletId = $slice->loadUint(32)->toInt();
                    $publicKey = $slice->loadBits(256);
                    $options = str_contains($walletClass, "\\V3\\WalletV3")
                        ? new WalletsSmc\V3\WalletV3Options(
                            publicKey: $publicKey,
                            walletId: $walletId,
                        )
                        : new WalletsSmc\V4\WalletV4Options(
                            publicKey: $publicKey,
                            walletId: $walletId,
                        );
                    $instance = new $walletClass($options);
                    break;

                case WalletsSmc\V5\WalletV5Beta::class:
                    $slice->skipBits(33); // seqno
                    $network = $slice->loadInt(32)->toInt();
                    $wc = $slice->loadInt(8)->toInt();
                    $walletVersionId = $slice->loadUint(8)->toInt();
                    $subwalletId = $slice->loadUint(32)->toInt();
                    $publicKey = $slice->loadBits(256);
                    $options = new WalletsSmc\V5\WalletV5Options(
                        publicKey: $publicKey,
                        walletId: new WalletsSmc\WalletId(
                            networkId: Network::from($network),
                            subwalletId: $subwalletId,
                            walletVersion: array_flip(WalletsSmc\V5\WalletV5Beta::WALLET_VERSIONS_MAP)[$walletVersionId] ?? "v5",
                            workchain: $wc,
                        ),
                        workchain: $wc,
                    );
                    $instance = new $walletClass($options);
                    break;

                default:
                    throw new \InvalidArgumentException("Unknown wallet class: " . $walletClass);
            }

            return new AddressPubkeyPair(
                $instance->getAddress(),
                $publicKey,
            );
        } catch (\Throwable $e) {
            throw new WalletReflectionException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array<string, string>
     */
    protected static function ensureHashes(): array
    {
        if (self::$HASHES === null) {
            $wallets = [
                WalletsSmc\Highload\HighloadWalletV2::class,

                WalletsSmc\Simple\SimpleWalletR1::class,
                WalletsSmc\Simple\SimpleWalletR2::class,
                WalletsSmc\Simple\SimpleWalletR3::class,

                WalletsSmc\V2\WalletV2R1::class,
                WalletsSmc\V2\WalletV2R2::class,

                WalletsSmc\V3\WalletV3R1::class,
                WalletsSmc\V3\WalletV3R2::class,

                WalletsSmc\V4\WalletV4R1::class,
                WalletsSmc\V4\WalletV4R2::class,

                WalletsSmc\V5\WalletV5Beta::class,
            ];

            foreach ($wallets as $walletClass) {
                self::$HASHES[$walletClass] = strtolower(call_user_func([$walletClass, "getCodeHash"]));
            }
        }

        return self::$HASHES;
    }
}
