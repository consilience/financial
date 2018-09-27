<?php

namespace Consilience\Iso8583\Message\Mapper;

/**
 * Interface MapperInterface
 *
 * @package Consilience\Iso8583\Message\Mapper
 */
interface MapperInterface
{

    /**
     * Packs the given property data
     *
     * @param string $data the property data
     * @param bool $encoded whether it has to be encoded
     *
     * @return string the packed data
     */
    public function pack(string $data, $encoded) : string;

    /**
     * Unpacks the given property data
     *
     * @param string $data the property data
     * @param bool $encoded whether it is encoded
     *
     * @return mixed the unpacked data
     */
    public function unpack(string $data, $encoded);
}
