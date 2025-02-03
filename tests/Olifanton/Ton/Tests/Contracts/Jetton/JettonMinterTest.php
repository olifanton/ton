<?php declare(strict_types=1);

namespace Olifanton\Ton\Tests\Contracts\Jetton;

use Brick\Math\BigInteger;
use Hamcrest\Core\IsEqual;
use Hamcrest\Type\IsArray;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Olifanton\Interop\Address;
use Olifanton\Interop\Units;
use Olifanton\Ton\Contracts\Jetton\JettonMinter;
use Olifanton\Ton\Contracts\Jetton\JettonMinterOptions;
use Olifanton\Ton\Contracts\Jetton\JettonWallet;
use Olifanton\Ton\Contracts\Jetton\MintOptions;
use Olifanton\Ton\Helpers\OffchainHelper;
use Olifanton\Ton\Transport;
use Olifanton\Ton\Transports\Toncenter\ToncenterResponseStack;
use PHPUnit\Framework\TestCase;

class JettonMinterTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    /**
     * @throws \Olifanton\Ton\Contracts\Exceptions\ContractException
     */
    public function testConstruct(): void
    {
        $instance = new JettonMinter(new JettonMinterOptions(
            new Address("UQBcCbR40Hzw9Gp6nVlcP8aILuHQuiW6jhSnaLG4TGG2Nsle"),
            "https://jetton.example.com/foo.json",
            JettonWallet::getDefaultCode(),
        ));

        $this->assertEquals(
            "UQDEgvMWV4rnD7q3MQmAmYXWc3VhiNrJR3x_TNHabIXKVZjR",
            $instance->getAddress()->toString(true, true, false),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testFromAddress(): void
    {
        $transportMock = \Mockery::mock(Transport::class);

        /** @var Transport & MockInterface $transportMock */
        // @phpstan-ignore-next-line
        $transportMock
            ->shouldReceive("runGetMethod")
            ->with(
                IsEqual::equalTo(new Address("UQDEgvMWV4rnD7q3MQmAmYXWc3VhiNrJR3x_TNHabIXKVZjR")),
                "get_jetton_data",
            )
            ->andReturnUsing(fn() => ToncenterResponseStack::parse(include STUB_DATA_DIR . "/stacks/get_jetton_data.php"));

        // @phpstan-ignore-next-line
        $instance = JettonMinter::fromAddress(
            $transportMock,
            new Address("UQDEgvMWV4rnD7q3MQmAmYXWc3VhiNrJR3x_TNHabIXKVZjR"),
        );

        $data = $instance->getJettonData($transportMock);
        $this->assertEquals(
            (new Address("EQBfiu4Ta8S1n9cgpnZDKqNlMqiFn2K7GGLEfc-h-GmghL7s"))->getHashPart(),
            $data->adminAddress->getHashPart(),
        );
        $this->assertEquals(
            "2000000",
            Units::fromNano($data->totalSupply),
        );
        $this->assertTrue($data->isMutable);
        $this->assertEquals("https://api.npoint.io/036c97bf516d3996c9b0", $data->jettonContentUrl);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateMintBody(): void
    {
        $body = JettonMinter::createMintBody(new MintOptions(
            Units::toNano("10000"),
            new Address("EQBfiu4Ta8S1n9cgpnZDKqNlMqiFn2K7GGLEfc-h-GmghL7s"),
            Units::toNano("1"),
            123,
        ));

        $slice = $body->beginParse();

        $this->assertEquals(BigInteger::of("21"), $slice->loadUint(32));
        $this->assertEquals(BigInteger::of("123"), $slice->loadUint(64));
        $this->assertEquals(
            (new Address("EQBfiu4Ta8S1n9cgpnZDKqNlMqiFn2K7GGLEfc-h-GmghL7s"))->getHashPart(),
            ($slice->loadAddress())->getHashPart(),
        );
        $this->assertEquals(Units::toNano("1"), $slice->loadCoins());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateChangeAdminBody(): void
    {
        $newAdminAddress = new Address("EQBfiu4Ta8S1n9cgpnZDKqNlMqiFn2K7GGLEfc-h-GmghL7s");
        $body = JettonMinter::createChangeAdminBody($newAdminAddress);
        $slice = $body->beginParse();

        $this->assertEquals(BigInteger::of("3"), $slice->loadUint(32));
        $this->assertEquals(BigInteger::zero(), $slice->loadUint(64));
        $this->assertEquals(
            $newAdminAddress->getHashPart(),
            $slice->loadAddress()->getHashPart(),
        );
    }

    /**
     * @throws \Throwable
     */
    public function testCreateEditContentBody(): void
    {
        $body = JettonMinter::createEditContentBody("https://exmple.com/jetton.json");
        $slice = $body->beginParse();

        $this->assertEquals(BigInteger::of("4"), $slice->loadUint(32));
        $this->assertEquals(BigInteger::zero(), $slice->loadUint(64));
        $ref = $slice->loadRef();
        $this->assertEquals("https://exmple.com/jetton.json", OffchainHelper::parseUrlCell($ref));
    }

    /**
     * @throws \Throwable
     */
    public function testGetJettonWalletAddress(): void
    {
        $instance = new JettonMinter(new JettonMinterOptions(
            new Address("UQBcCbR40Hzw9Gp6nVlcP8aILuHQuiW6jhSnaLG4TGG2Nsle"),
            "https://jetton.example.com/foo.json",
            JettonWallet::getDefaultCode(),
        ));

        $ownerAddress = new Address("EQBfiu4Ta8S1n9cgpnZDKqNlMqiFn2K7GGLEfc-h-GmghL7s");
        /** @var Transport & MockInterface $transportMock */
        $transportMock = \Mockery::mock(Transport::class);
        // @phpstan-ignore-next-line
        $transportMock
            ->shouldReceive("runGetMethod")
            ->with(
                IsEqual::equalTo($instance),
                "get_wallet_address",
                IsArray::arrayValue(),
            )
            ->andReturnUsing(fn() => ToncenterResponseStack::parse(include STUB_DATA_DIR . "/stacks/get_wallet_address.php"));

        $walletAddress = $instance->getJettonWalletAddress($transportMock, $ownerAddress);
        $this->assertEquals(
            (new Address("EQCUcYj6GX4rl0PgcLeG1r3KKED4lbl-6la8BiniFRXwD1Y6"))->getHashPart(),
            $walletAddress->getHashPart(),
        );
    }
}
