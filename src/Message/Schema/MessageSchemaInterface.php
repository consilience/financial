<?php

namespace Consilience\Iso8583\Message\Schema;

/**
 * Interface MessageSchemaInterface
 *
 * @package Consilience\Iso8583\Message\Schema
 */
interface MessageSchemaInterface
{

    /**
     * Gets the schema name
     *
     * @return string
     */
    public function getName(): string;
}
