<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Connect;

use Cake\Cache\Engine\ArrayEngine;
use GuzzleHttp\Psr7\Response;
use Http\Client\Common\HttpMethodsClientInterface;
use Mockery\MockInterface;
use Olifanton\Ton\Connect\Models\BridgeType;
use Olifanton\Ton\Connect\Models\WalletApplication;
use Olifanton\Ton\Connect\WalletApplicationsManager;
use Overtrue\PHPLint\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

class WalletApplicationsManagerTest extends TestCase
{
    protected HttpMethodsClientInterface & MockInterface $httpClientMock;

    protected function setUp(): void
    {
        $this->httpClientMock = \Mockery::mock(HttpMethodsClientInterface::class); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function getInstance(bool $wCache = false): WalletApplicationsManager
    {
        $c = null;

        if ($wCache) {
            $c = new ArrayEngine();
            $c->init();
        }

        $instance = new WalletApplicationsManager(
            $this->httpClientMock,
            $c,
        );
        $instance->setLogger(new Logger());

        return $instance;
    }

    /**
     * @throws \Throwable
     */
    public function testGetDefaultApps(): void
    {
        $apps = WalletApplicationsManager::getDefaultApps();

        $this->assertCount(4, $apps);

        $tk = $apps[1];

        $this->assertEquals("tonkeeper", $tk->appName);
        $this->assertEquals("Tonkeeper", $tk->name);
        $this->assertEquals(BridgeType::SSE, $tk->bridge[0]->type);
        $this->assertEquals("https://bridge.tonapi.io/bridge", $tk->bridge[0]->url);

        $tgWallet = $apps[0];
        $this->assertEquals("telegram-wallet", $tgWallet->appName);
    }

    /**
     * @throws \Throwable
     */
    public function testDownloadList(): void
    {
        // Stubs
        $json = trim(file_get_contents(STUB_DATA_DIR . "/connect/wallets.json"));

        // Mocks
        $this
            ->httpClientMock
            ->shouldReceive("get")
            ->andReturn(new Response(
                headers: [
                    "Content-Type" => "application/json",
                ],
                body: $json
            ));

        // Test
        $wallets = $this->getInstance()->getList();

        $this->assertCount(8, $wallets);
        $this->assertTonkeeper($wallets[0]);
    }

    /**
     * @throws \Throwable
     */
    public function testDownloadListV2(): void
    {
        // Stubs
        $json = trim(file_get_contents(STUB_DATA_DIR . "/connect/wallets-v2.json"));

        // Mocks
        $this
            ->httpClientMock
            ->shouldReceive("get")
            ->andReturn(new Response(
                headers: [
                    "Content-Type" => "application/json",
                ],
                body: $json
            ));

        // Test
        $wallets = $this->getInstance()->getList();

        $this->assertCount(8, $wallets);
        $this->assertTonkeeper($wallets[1]);
    }

    /**
     * @throws \Throwable
     */
    public function testDownloadListWithCache(): void
    {
        // Stubs
        $json = file_get_contents(STUB_DATA_DIR . "/connect/wallets.json");

        // Mocks
        $this
            ->httpClientMock
            ->shouldReceive("get")
            ->andReturn(new Response(
                headers: [
                    "Content-Type" => "application/json",
                ],
                body: $json
            ))
            ->once();

        // Test
        $instance = $this->getInstance(wCache: true);
        $wallets = $instance->getList();
        $wallets = $instance->getList();
        $wallets = $instance->getList();
        $wallets = $instance->getList();
        $wallets = $instance->getList();

        $this->assertCount(8, $wallets);

        $this->assertTonkeeper($wallets[0]);
    }

    /**
     * @throws \Throwable
     */
    public function testDownloadListWithPredefinedCache(): void
    {
        // Stubs
        $json = file_get_contents(STUB_DATA_DIR . "/connect/wallets.json");

        // Mocks
        $this
            ->httpClientMock
            ->shouldReceive("get")
            ->never();

        /** @var CacheInterface & MockInterface $cache */
        $cache = \Mockery::mock(CacheInterface::class);
        $cache
            ->shouldReceive("get")
            ->twice()
            ->andReturn(json_decode($json, true));

        // Test
        $instance = $this->getInstance();
        $instance->setCache($cache);
        $wallets = $instance->getList();
        $wallets = $instance->getList();

        $this->assertCount(8, $wallets);
        $this->assertTonkeeper($wallets[0]);
    }

    /**
     * @throws \Throwable
     */
    public function testFallbackList(): void
    {
        $this->httpClientMock->shouldReceive("get")->andThrow(new \RuntimeException());
        $instance = $this->getInstance();
        $instance->setLogger(new NullLogger());

        $wallets = $instance->getList();
        $this->assertCount(4, $wallets);
        $this->assertTonkeeper($wallets[1]);
    }

    private function assertTonkeeper(WalletApplication $app): void
    {
        $this->assertEquals("tonkeeper", $app->appName);
        $this->assertEquals("Tonkeeper", $app->name);
        $this->assertEquals("https://tonkeeper.com/assets/tonconnect-icon.png", $app->image);
        $this->assertEquals("https://tonkeeper.com", $app->aboutUrl);
        $this->assertEquals("https://app.tonkeeper.com/ton-connect", $app->universalUrl);
        $this->assertEquals(BridgeType::SSE, $app->bridge[0]->type);
        $this->assertEquals("https://bridge.tonapi.io/bridge", $app->bridge[0]->url);
    }
}
