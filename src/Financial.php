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
     *
     * @return MessagePacker
     */
    public function pack(SchemaManager $schemaManager): MessagePacker
    {
        return new MessagePacker($this->cacheManager, $schemaManager);
    }

    /**
     * Returns an instance of the message unpacker
     *
     * @param SchemaManager $schemaManager
     *
     * @return MessageUnpacker
     */
    public function unpack(SchemaManager $schemaManager): MessageUnpacker
    {
        return new MessageUnpacker($this->cacheManager, $schemaManager);
    }
}
