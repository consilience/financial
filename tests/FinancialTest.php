<?php

namespace Consilience\Iso8583\Tests;

use Consilience\Iso8583\Cache\CacheManager;
use Consilience\Iso8583\Financial;
use Consilience\Iso8583\Message\Packer\MessagePacker;
use Consilience\Iso8583\Message\Schema\Exception\DefinitionNotFoundException;
use Consilience\Iso8583\Message\Schema\ISO8583;
use Consilience\Iso8583\Message\Schema\MessageSchemaInterface;
use Consilience\Iso8583\Message\Schema\SchemaManager;
use Consilience\Iso8583\Message\Unpacker\MessageUnpacker;
use PHPUnit\Framework\TestCase;

/**
 * Class FinancialTest
 *
 * @package Consilience\Iso8583\Tests
 */
class FinancialTest extends TestCase
{
    const DUMMY_MTI           = '0200';
    const DUMMY_PAN           = '1029384756193749';
    const DUMMY_HEADER_LENGTH = 2;

    /** @test */
    public function pack()
    {
        $cacheManager = new CacheManager();

        /** @var ISO8583 $schemaManager */
        $schemaManager = new SchemaManager(new ISO8583(), $cacheManager);

        $schemaManager->setPan(self::DUMMY_PAN);
        $schemaManager->setCurrencyCodeCardholderBilling('GBP');
        $schemaManager->setPrivateReserved6('sample');

        $messagePacker = (new Financial($cacheManager))->pack($schemaManager);

        $messagePacker->setMti(self::DUMMY_MTI);
        $messagePacker->setHeaderLength(self::DUMMY_HEADER_LENGTH);

        $this->assertInstanceOf(MessagePacker::class, $messagePacker);

        $this->assertEquals(
            '003430323030c000000000002000000000000000008031363130323933383437353631393337343947425030303673616d706c65',
            $messagePacker->generate()
        );
    }

    /** @test */
    public function packNonEncoded()
    {
        $cacheManager = new CacheManager();

        /** @var ISO8583 $schemaManager */
        $schemaManager = new SchemaManager(new ISO8583(), $cacheManager);

        $schemaManager->setPan(self::DUMMY_PAN);
        $schemaManager->setCurrencyCodeCardholderBilling('GBP');
        $schemaManager->setPrivateReserved6('sample');
        $schemaManager->setMessageAuthenticationCode('0000000000000001');

        $messagePacker = (new Financial($cacheManager))->pack($schemaManager, false);

        $messagePacker->setMti(bin2hex(self::DUMMY_MTI));
        $messagePacker->setHeaderLength(self::DUMMY_HEADER_LENGTH);

        $this->assertInstanceOf(MessagePacker::class, $messagePacker);

        $this->assertEquals(
            '8830323030c0000000000020010000000000000080161029384756193749GBP0000000000000001006sample',
            $messagePacker->generate()
        );
    }

    /** @test */
    public function unpack()
    {
        $cacheManager = new CacheManager();

        /** @var ISO8583 $schemaManager */
        $schemaManager = new SchemaManager(new ISO8583(), $cacheManager);

        $messageUnpacker = (new Financial($cacheManager))->unpack($schemaManager);

        $isoMessage = "012430323030f23e4491a8e0802000000000000000203136313032393338343735363139333734393030303030303" .
            "0303030303030303130303031323132313435343038303030303039313435343038313231323137303331323133303030303930" .
            "3230304330303030303030303036303030303230303630303030323033372a2a2a2a2a2a2a2a2a2a2a2a2a3d2a2a2a2a2a2a2a2" .
            "a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a504652333437303030303039323837353937353830303030303030303030333039333733" .
            "303134373430342054657374204167656e74203220204861746669656c642020202020486547423832363032383030303030303" .
            "23136317c303030307c504652333437303030303039303135353630313031323634303243313031";

        $messageUnpacker->setHeaderLength(self::DUMMY_HEADER_LENGTH);

        $unpackedMessage = $messageUnpacker->parse($isoMessage);

        /** @var ISO8583 $schema */
        $schema = $schemaManager->getSchema();

        $this->assertInstanceOf(MessageUnpacker::class, $messageUnpacker);
        $this->assertEquals(self::DUMMY_MTI, $unpackedMessage->getMti());
        $this->assertEquals(self::DUMMY_PAN, $schema->getPan());
    }

    /** @test */
    public function unpackNonEncoded()
    {
        $cacheManager = new CacheManager();

        /** @var ISO8583 $schemaManager */
        $schemaManager = new SchemaManager(new ISO8583(), $cacheManager);

        $messageUnpacker = (new Financial($cacheManager))->unpack($schemaManager, false);

        $isoMessage = '840200c0000000000020010000000000000080161029384756193749GBP0000000000000001006sample';

        $messageUnpacker->setHeaderLength(self::DUMMY_HEADER_LENGTH);

        $unpackedMessage = $messageUnpacker->parse($isoMessage);

        /** @var ISO8583 $schema */
        $schema = $schemaManager->getSchema();

        $this->assertInstanceOf(MessageUnpacker::class, $messageUnpacker);
        $this->assertEquals(self::DUMMY_MTI, $unpackedMessage->getMti());
        $this->assertEquals(self::DUMMY_PAN, $schema->getPan());
        $this->assertEquals('GBP', $schema->getCurrencyCodeCardholderBilling());
        $this->assertEquals('sample', $schema->getPrivateReserved6());
    }

    /** @test */
    public function exceptionOnUndefinedBit()
    {
        $cacheManager = new CacheManager();

        $schemaManager = new SchemaManager(new class implements MessageSchemaInterface
        {
            /**
             * Gets the schema name
             *
             * @return string
             */
            public function getName() : string
            {
                return "Dummy schema";
            }
        }, $cacheManager);

        $messageUnpacker = (new Financial($cacheManager))->unpack($schemaManager, false);

        $isoMessage = '840200c0000000000020010000000000000080161029384756193749GBP0000000000000001006sample';

        $messageUnpacker->setHeaderLength(self::DUMMY_HEADER_LENGTH);

        try {
            $messageUnpacker->parse($isoMessage);
        } catch (DefinitionNotFoundException $e) {
            $this->assertEquals('Bit 2 was not found in the schema cache', $e->getMessage());
            return;
        }

        $this->fail('An excepted exception was not thrown.');
    }
}
