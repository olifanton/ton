<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Transports\Toncenter\ToncenterTransport;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Slice;
use Olifanton\Ton\Transports\Toncenter\ToncenterResponseStack;
use PHPUnit\Framework\TestCase;

class ToncenterResponseStackTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testParseNumAndCell(): void
    {
        $stack = ToncenterResponseStack::parse([
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
        $stack = ToncenterResponseStack::parse([
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
        $stack = ToncenterResponseStack::parse([
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

    /**
     * @throws \Throwable
     */
    public function testSerialization(): void
    {
        $stack = ToncenterResponseStack::parse([
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
        $hibernated = unserialize(serialize($stack));
        
        $this->assertEquals($stack, $hibernated);
    }

    /**
     * @throws \Throwable
     */
    public function testParseSlice(): void
    {
        $stack = ToncenterResponseStack::parse([
            [
                'tuple',
                [
                    'elements' => [
                        'slice' => [
                            '@type' => 'tvm.slice',
                            'bytes' => 'te6cckEBAQEAJAAAQ4AAfVvsWElajYlLb4F8fIyqLMQ5C7fmIG3GgSHEjI54E7D+9neY',
                        ],
                    ],
                ]
            ],
        ]);

        $this->assertCount(1, $stack);
        $slice = $stack->currentTuple()['slice'];
        $this->assertInstanceOf(Slice::class, $slice);
        $address = $slice->loadAddress()->toString(true, true, true);
        $this->assertEquals('EQAD6t9iwkrUbEpbfAvj5GVRZiHIXb8xA240CQ4kZHPAnSuo', $address);
    }
}
