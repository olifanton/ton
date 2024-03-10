<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Connect;

use Olifanton\Ton\Connect\Models\BridgeType;
use Olifanton\Ton\Connect\WalletApplicationsManager;
use PHPUnit\Framework\TestCase;

class WalletApplicationsManagerTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testGetDefaultApps(): void
    {
        $apps = WalletApplicationsManager::getDefaultApps();

        $this->assertCount(3, $apps);

        $tk = $apps[0];

        $this->assertEquals("tonkeeper", $tk->appName);
        $this->assertEquals("Tonkeeper", $tk->name);
        $this->assertEquals(BridgeType::SSE, $tk->bridge[0]->type);
        $this->assertEquals("https://bridge.tonapi.io/bridge", $tk->bridge[0]->url);
    }
}
