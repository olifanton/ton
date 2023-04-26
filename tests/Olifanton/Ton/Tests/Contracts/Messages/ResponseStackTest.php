<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Messages;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contracts\Messages\ResponseStack;
use PHPUnit\Framework\TestCase;

class ResponseStackTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testParseNumAndCell(): void
    {
        $stack = ResponseStack::parse([
            [
                'num',
                '0x20',
            ],
            [
                'cell',
                [
                    'bytes' => 'te6cckEBAQEAJgAAR7qTgBxn2mdIXWQGQLPy+yGODfCAF2StpkJzlOD+Ksla0zj7sO/0J1M=',
                    'object' => [
                        'data' => [
                            'b64' => 'upOAHGfaZ0hdZAZAs/L7IY4N8IAXZK2mQnOU4P4qyVrTOPug',
                            'len' => 283,
                        ],
                        'refs' => [],
                    ],
                ],
            ],
        ]);

        $this->assertCount(2, $stack);
        $this->assertEquals(32, $stack->currentBigInteger()->toInt());
        $stack->next();
        $this->assertInstanceOf(Cell::class, $stack->currentCell());
    }

    /**
     * @throws \Throwable
     */
    public function testParseNumAndCell2(): void
    {
        $stack = ResponseStack::parse([
            [
                'num',
                '0x58',
            ],
            [
                'cell',
                [
                    'bytes' => 'te6cckEBAQEAJgAAR7qTgAfXdYmRBEAMd3t9yxv55pMFZOeCDpxOihKYCFsErLOnkJmtlJ8=',
                    'object' => [
                        'data' => [
                            'b64' => 'upOAB9d1iZEEQAx3e33LG/nmkwVk54IOnE6KEpgIWwSss6eA',
                            'len' => 283,
                        ],
                        'refs' => [],
                    ],
                ],
            ],
        ]);

        $this->assertCount(2, $stack);
        $this->assertEquals(88, $stack->currentBigInteger()->toInt());
        $stack->next();
        $this->assertInstanceOf(Cell::class, $stack->currentCell());
    }

    /**
     * @throws \Throwable
     */
    public function testParseNumAndEmptyList(): void
    {
        $stack = ResponseStack::parse([
            [
                'num',
                '0x8',
            ],
            [
                'list',
                [
                    '@type' => 'tvm.list',
                    'elements' => [],
                ],
            ],
        ]);

        $this->assertCount(2, $stack);
        $this->assertEquals(8, $stack->currentBigInteger()->toInt());
        $stack->next();
        $list = $stack->currentList();
        $this->assertIsArray($list);
    }
}
