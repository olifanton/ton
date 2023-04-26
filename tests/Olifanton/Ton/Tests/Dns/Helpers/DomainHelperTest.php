<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Dns\Helpers;

use Olifanton\Ton\Dns\Exceptions\DnsException;
use Olifanton\Ton\Dns\Helpers\DomainHelper;
use Olifanton\TypedArrays\Uint8Array;
use PHPUnit\Framework\TestCase;

class DomainHelperTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testDomainToBytes(): void
    {
        $cases = [
            "foundation.ton" => [116, 111, 110, 0, 102, 111, 117, 110, 100, 97, 116, 105, 111, 110, 0],
            "." => [0],
            "foo0-barbaz.ton" =>  [116, 111, 110,  0, 102, 111, 111, 48, 45, 98,  97, 114, 98, 97, 122, 0],
            "foo.bar.ton" => [116, 111, 110, 0, 98, 97, 114, 0, 102, 111, 111, 0],
        ];

        foreach ($cases as $domain => $expectedUint) {
            $rawDomain = DomainHelper::domainToBytes($domain);
            $this
                ->assertEquals(
                    $expectedUint,
                    $this->toArr($rawDomain),
                    $domain,
                );
        }
    }

    /**
     * @throws \Throwable
     */
    public function testIllegalCharacters(): void
    {
        $this->expectException(DnsException::class);
        $this->expectExceptionMessage("Domain contains illegal characters");
        DomainHelper::domainToBytes("illegal`.ton");
    }

    /**
     * @return int[]
     */
    private function toArr(Uint8Array $a): array
    {
        $result = [];

        for ($i = 0; $i < $a->length; $i++) {
            $result[] = $a[$i];
        }

        return $result;
    }
}
