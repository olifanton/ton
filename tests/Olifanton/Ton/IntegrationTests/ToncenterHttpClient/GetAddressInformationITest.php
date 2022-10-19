<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Ton\Models\AddressState;
use Olifanton\Ton\Toncenter\Exceptions\ClientException;
use Olifanton\Utils\Address;

class GetAddressInformationITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();
        $resp = $client->getAddressInformation(new Address("Ef8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAU"));
        $this->assertEquals(AddressState::ACTIVE, $resp->state);
    }

    /**
     * @throws \Throwable
     */
    public function testFailedIncorrectAddress(): void
    {
        $client = $this->getInstance();
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage("Incorrect address");
        $this->expectExceptionCode(416);
        $client->jsonRPC([
            "method" => "getAddressInformation",
            "params" => [
                "address" => "EQDxxpeLM0R2HH3nmtDoQsRL959eYb4pRW1tNL257U30KB0X",
            ],
        ]);
    }
}
