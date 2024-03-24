<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Helpers;

use Olifanton\Ton\Helpers\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testOneStringValid(): void
    {
        $data = [
            "foo" => "bar",
        ];

        $this
            ->assertNull(
                Validator::validate(
                    $data,
                    [
                        Validator::STRING,
                        "foo",
                    ]
                ),
            );
    }

    public function testOneStringWrongType(): void
    {
        $data = [
            "foo" => 1,
        ];

        $this
            ->assertEquals(
                ['"foo" is not a string'],
                Validator::validate(
                    $data,
                    [
                        Validator::STRING,
                        "foo",
                    ]
                ),
            );
    }

    public function testRequiredValid(): void
    {
        $data = [
            "foo" => "bar",
        ];

        $this
            ->assertNull(
                Validator::validate(
                    $data,
                    [
                        Validator::REQUIRED,
                        "foo",
                    ]
                ),
            );
    }

    public function testRequiredArrayValid(): void
    {
        $data = [
            "foo" => "bar",
            "baz" => 1,
        ];

        $this
            ->assertNull(
                Validator::validate(
                    $data,
                    [
                        Validator::REQUIRED,
                        ["foo", "baz"],
                    ]
                ),
            );
    }

    public function testRequiredInvalid(): void
    {
        $data = [
            "foo" => "bar",
        ];

        $this
            ->assertEquals(
                ['"bar" is required'],
                Validator::validate(
                    $data,
                    [
                        Validator::REQUIRED,
                        "bar",
                    ]
                ),
            );
    }

    public function testValidateRuleInnerRuleValid(): void
    {
        $data = [
            "foo" => [
                "bar" => true,
                "baz" => [
                    "foobarbaz" => [1, 2, 3],
                    "foobar" => "string",
                ]
            ]
        ];

        $this
            ->assertNull(
                Validator::validate(
                    $data,
                    [
                        Validator::RULE,
                        ["foo"],
                        [
                            [
                                Validator::BOOL,
                                "bar",
                            ],
                            [
                                Validator::RULE,
                                "baz",
                                [
                                    [
                                        Validator::STRING,
                                        "foobar",
                                    ],
                                ],
                            ],
                            [
                                Validator::ARRAY,
                                "foobarbaz",
                            ]
                        ],
                    ],
                ),
            );
    }

    public function testValidateRuleInnerRuleInvalid(): void
    {
        $data = [
            "foo" => [
                "bar" => true,
                "baz" => [
                    "foobar" => 1.23,
                ]
            ]
        ];

        $this
            ->assertEquals(
                ['"foo" is not an valid array: "baz" is not an valid array: "foobar" is not a boolean'],
                Validator::validate(
                    $data,
                    [
                        Validator::RULE,
                        ["foo"],
                        [
                            [
                                Validator::BOOL,
                                "bar",
                            ],
                            [
                                Validator::RULE,
                                "baz",
                                [
                                    [
                                        Validator::BOOL,
                                        "foobar",
                                    ],
                                ],
                            ],
                        ]
                    ],
                ),
            );
    }

    public function testMultipleErrors(): void
    {
        $data = [
            "bar" => false,
            "k0" => "str",
            "k1" => "str",
            "k2" => "str",
        ];

        $this
            ->assertEquals(
                [
                    '"foo" is required',
                    '"baz" is required',
                    '"bar" is not a numeric',
                    '"k0" is not a float',
                    '"k1" is not a float',
                    '"k2" is not a integer',
                ],
                Validator::validate(
                    $data,
                    [
                        Validator::REQUIRED,
                        ["foo", "baz"],
                    ],
                    [
                        Validator::NUM,
                        "bar",
                    ],
                    [
                        Validator::FLOAT,
                        ["k0", "k1"],
                    ],
                    [
                        Validator::INT,
                        "k2",
                    ]
                ),
            );
    }
}
