<?php declare(strict_types=1);

namespace Olifanton\Ton\IntegrationTests\ToncenterHttpClient;

class DetectAddressITest extends ToncenterHttpClientITestCase
{
    /**
     * @throws \Throwable
     */
    public function testFriendlyBounceable(): void
    {
        $client = $this->getInstance();
        $result = $client->detectAddress("EQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqB2N");

        $this->assertEquals(
            "0:83dfd552e63729b472fcbcc8c45ebcc6691702558b68ec7527e1ba403a0f31a8",
            $result->rawForm,
        );
        $this->assertEquals(
            "EQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqB2N",
            $result->bounceable->b64,
        );
        $this->assertEquals(
            "EQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqB2N",
            $result->bounceable->b64url,
        );
        $this->assertEquals(
            "UQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqEBI",
            $result->nonBounceable->b64,
        );
        $this->assertEquals(
            "UQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqEBI",
            $result->nonBounceable->b64url,
        );
        $this->assertEquals(
            "friendly_bounceable",
            $result->givenType,
        );
        $this->assertFalse($result->testOnly);
    }

    /**
     * @throws \Throwable
     */
    public function testRawForm(): void
    {
        $client = $this->getInstance();
        $result = $client->detectAddress("0:83dfd552e63729b472fcbcc8c45ebcc6691702558b68ec7527e1ba403a0f31a8");

        $this->assertEquals(
            "0:83dfd552e63729b472fcbcc8c45ebcc6691702558b68ec7527e1ba403a0f31a8",
            $result->rawForm,
        );
        $this->assertEquals(
            "EQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqB2N",
            $result->bounceable->b64,
        );
        $this->assertEquals(
            "EQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqB2N",
            $result->bounceable->b64url,
        );
        $this->assertEquals(
            "UQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqEBI",
            $result->nonBounceable->b64,
        );
        $this->assertEquals(
            "UQCD39VS5jcptHL8vMjEXrzGaRcCVYto7HUn4bpAOg8xqEBI",
            $result->nonBounceable->b64url,
        );
        $this->assertEquals(
            "raw_form",
            $result->givenType,
        );
        $this->assertFalse($result->testOnly);
    }
}
