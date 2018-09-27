<?php

namespace Consilience\Iso8583\Tests\Message\Schema;

use DateTime;
use Consilience\Iso8583\Cache\CacheManager;
use PHPUnit\Framework\TestCase;
use Consilience\Iso8583\Message\Schema\ISO8583;

/**
 * Class ISO8583Test
 *
 * @package Consilience\Iso8583\Tests\Message\Schema
 */
class ISO8583Test extends TestCase
{

    /** @test */
    public function getSchemaName()
    {
        $this->assertSame(ISO8583::SCHEMA_NAME, (new ISO8583())->getName());
    }

    /** @test */
    public function settersAndGetters()
    {
        $schema = new ISO8583();

        $cacheManager = new CacheManager();
        $schemaCache  = $cacheManager->generateSchemaCache($schema);

        foreach ($schemaCache->getSchemaData() as $field) {
            $methodSetterName = 'set' . ucfirst($field['property']);
            $methodGetterName = 'get' . ucfirst($field['property']);

            $data = '';
            switch ($field['var']) {
                case 'string':
                    $data = 'testing';
                    break;
                case 'int':
                    $data = 12345;
                    break;
                case 'DateTime':
                    $data = new DateTime();
                    break;
                default:
                    $this->markTestIncomplete('Schema data must be a string, int or DateTime');
                    break;
            }

            $schema->{$methodSetterName}($data);

            $result = $schema->{$methodGetterName}();

            $this->assertEquals($data, $result);
        }
    }
}
