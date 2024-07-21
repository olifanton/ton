<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Http\Client\Common\HttpMethodsClientInterface;
use Olifanton\Ton\Connect\Models\WalletApplication;
use Olifanton\Ton\Helpers\Validator;
use Olifanton\Ton\Marshalling\Exceptions\MarshallingException;
use Olifanton\Ton\Marshalling\Json\Hydrator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;

class WalletApplicationsManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const TG_WALLET = "telegram-wallet";
    public const TONKEEPER = "tonkeeper";
    public const TONHUB = "tonhub";
    public const MYTONWALLET = "mytonwallet";

    protected const WALLETS_LIST_URL = "https://raw.githubusercontent.com/ton-blockchain/wallets-list/main/wallets-v2.json";

    protected const CACHE_KEY = "olfnt_conn_wallets_list";

    protected int $cacheTtl = 3600;

    public function __construct(
        private readonly HttpMethodsClientInterface $httpClient,
        private ?CacheInterface $cache = null,
    ) {}

    /**
     * @return WalletApplication[]
     * @throws MarshallingException
     */
    public function getList(): array
    {
        $jsonList = $this->readCachedList();

        if (!$jsonList) {
            try {
                $response = $this
                    ->httpClient
                    ->get(self::WALLETS_LIST_URL);
                $body = $response->getBody()->getContents();
                $json = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
                $jsonList = $this->validateAndCleanup($json);

                if ($jsonList) {
                    $this->writeListCache($jsonList, $this->cacheTtl);
                }
            } catch (\Throwable $e) {
                $this
                    ->logger
                    ?->error("Wallets list downloading error: " . $e->getMessage(), [
                        "exception" => $e,
                    ]);
            }
        }

        if ($jsonList) {
            return array_map(
                static fn(array $row) => Hydrator::extract(WalletApplication::class, $row),
                $jsonList,
            );
        }

        return self::getDefaultApps();
    }

    public function setCache(?CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function setCacheTtl(int $seconds): void
    {
        $this->cacheTtl = $seconds;
    }

    /**
     * @return WalletApplication[]
     * @throws MarshallingException
     */
    public static function getDefaultApps(): array
    {
        return [
            WalletApplication::create(
                "telegram-wallet",
                "Wallet",
                "https://wallet.tg/images/logo-288.png",
                "https://t.me/wallet?attach=wallet",
                [
                    [
                        "type" => "sse",
                        "url" => "https://bridge.ton.space/bridge",
                    ],
                ],
                ["ios", "android", "macos", "windows", "linux"],
                aboutUrl: "https://wallet.tg/",
            ),
            WalletApplication::create(
                "tonkeeper",
                "Tonkeeper",
                "https://tonkeeper.com/assets/tonconnect-icon.png",
                "https://app.tonkeeper.com/ton-connect",
                [
                    [
                        "type" => "sse",
                        "url" => "https://bridge.tonapi.io/bridge",
                    ],
                ],
                ["ios", "android", "chrome", "firefox", "macos"],
                aboutUrl: "https://tonkeeper.com",
            ),
            WalletApplication::create(
                "tonhub",
                "Tonhub",
                "https://tonhub.com/tonconnect_logo.png",
                "https://tonhub.com/ton-connect",
                [
                    [
                        "type" => "sse",
                        "url" => "https://connect.tonhubapi.com/tonconnect",
                    ],
                ],
                ["ios", "android"],
                aboutUrl: "https://tonhub.com",
            ),
            WalletApplication::create(
                "mytonwallet",
                "MyTonWallet",
                "https://mytonwallet.io/icon-256.png",
                "https://connect.mytonwallet.org",
                [
                    [
                        "type" => "sse",
                        "url" => "https://tonconnectbridge.mytonwallet.org/bridge/",
                    ],
                ],
                ["chrome", "windows", "macos", "linux"],
                aboutUrl: "https://mytonwallet.io",
            ),
        ];
    }

    /**
     * @throws MarshallingException
     */
    public static function getFromDefaults(string $appName): ?WalletApplication
    {
        foreach (self::getDefaultApps() as $app) {
            if ($app->appName === $appName) {
                return $app;
            }
        }

        return null;
    }

    /**
     * @return array[]|null
     */
    protected function readCachedList(): ?array
    {
        try {
            return $this->cache?->get(self::CACHE_KEY);
            // @codeCoverageIgnoreStart
        } catch (CacheException $e) {
            $this
                ->logger
                ?->warning(
                    "Cache reading error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                    ]
                );
        }
        // @codeCoverageIgnoreEnd

        return null;
    }

    /**
     * @param array[] $list
     */
    protected function writeListCache(array $list, int $ttl): void
    {
        try {
            $this
                ->cache
                ?->set(
                    self::CACHE_KEY,
                    $list,
                    $ttl,
                );
            // @codeCoverageIgnoreStart
        } catch (CacheException $e) {
            $this
                ->logger
                ?->warning(
                    "Cache writing error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                    ]
                );
        }
        // @codeCoverageIgnoreEnd
    }

    private function validateAndCleanup(array $data): ?array
    {
        try {
            $result = [];

            foreach ($data as $row) {
                $v = Validator::validate(
                    $row,
                    [
                        Validator::REQUIRED,
                        [
                            "app_name",
                            "name",
                            "image",
                            "platforms",
                            "bridge",
                        ],
                    ],
                    [
                        Validator::STRING,
                        [
                            "app_name",
                            "name",
                            "image",
                        ],
                    ],
                    [
                        Validator::ARRAY,
                        [
                            "platforms",
                            "bridge",
                        ],
                    ],
                );

                if (!$v) {
                    $result[] = $row;
                    continue;
                }

                $this
                    ->logger
                    ?->warning(
                        "Wallets json row validation error: ",
                        [
                            "errors" => $v,
                        ],
                    );
            }

            return !empty($result) ? $result : null;
        } catch (\Throwable $e) {
            $this
                ->logger
                ?->warning(
                    "Wallets json validation error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                    ],
                );
        }

        return null;
    }
}
