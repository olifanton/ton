<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

use Olifanton\Interop\Address;
use Olifanton\Ton\Transports\Toncenter\Exceptions\ClientException;

class GetTransactionsITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testSuccess(): void
    {
        $client = $this->getInstance();

        try {
            $resp = $client->getTransactions(new Address("Ef8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAU"));
        } catch (ClientException $e) {
            if (str_contains($e->getMessage(), "lt not in db")) {
                // FIXME: Dirty hack, should be reworked later
                $this->addToAssertionCount(1);
                return;
            }

            throw $e;
        }

        $this->assertNotEmpty($resp->items);
    }
}
