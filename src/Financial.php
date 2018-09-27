<?php

namespace Consilience\Iso8583;

use Consilience\Iso8583\Cache\CacheManager;
use Consilience\Iso8583\Message\Packer\MessagePacker;
use Consilience\Iso8583\Message\Schema\SchemaManager;
use Consilience\Iso8583\Message\Unpacker\MessageUnpacker;

/**
 * Class Financial
 *
 * @package Consilience\Iso8583
 */
class Financial
{

    /** @var CacheManager $cacheManager */
    protected $cacheManager;

    /**
     * Financial constructor.
     *
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Returns an instance of the message packer
     *
     * @param SchemaManager $schemaManager
     * @param bool $encoded
     *
     * @return MessagePacker
     */
    public function pack(SchemaManager $schemaManager, $encoded = true) : MessagePacker
    {
        return new MessagePacker($this->cacheManager, $schemaManager, $encoded);
    }

    /**
     * Returns an instance of the message unpacker
     *
     * @param SchemaManager $schemaManager
     * @param bool $encoded
     *
     * @return MessageUnpacker
     */
    public function unpack(SchemaManager $schemaManager, $encoded = true) : MessageUnpacker
    {
        return new MessageUnpacker($this->cacheManager, $schemaManager, $encoded);
    }
}
