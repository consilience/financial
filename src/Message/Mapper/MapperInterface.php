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
     *
     * @return string the packed data
     */
    public function pack(string $data): string;

    /**
     * Unpacks the given property data
     *
     * @param string $data the property data
     *
     * @return mixed the unpacked data
     */
    public function unpack(string $data);
}
