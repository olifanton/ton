<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Helpers;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Helpers\OffchainHelper;
use PHPUnit\Framework\TestCase;

class OffchainHelperTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateUrlCell(): void
    {
        $cell = OffchainHelper::createUrlCell("https://example.com/foo/bar/baz?t=123");
        $this->assertEquals(
            <<<FIFI_PRINT
            x{0168747470733A2F2F6578616D706C652E636F6D2F666F6F2F6261722F62617A3F743D313233}
            FIFI_PRINT,
            trim($cell->print()),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testParseUrlCell(): void
    {
        $cell = Cell::oneFromBoc("b5ee9c7241010101002800004c0168747470733a2f2f6578616d706c652e636f6d2f666f6f2f6261722f62617a3f743d31323345d67edd");
        $url = OffchainHelper::parseUrlCell($cell);

        $this->assertEquals(
            "https://example.com/foo/bar/baz?t=123",
            $url,
        );
    }
}
