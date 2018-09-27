<?php

namespace Consilience\Iso8583\Cache;

use Consilience\Iso8583\Container\PropertyAnnotationContainer;
use Illuminate\Support\Collection;

/**
 * Class CacheFile
 *
 * @package Consilience\Iso8583\Cache
 */
class CacheFile
{

    /** @var Collection $schemaCache the cached file contents */
    protected $schemaCache;

    /**
     * CacheFile constructor.
     *
     * @param string $schemaCache the cached file contents
     */
    public function __construct($schemaCache)
    {
        $this->schemaCache = $this->parseSchemaData($schemaCache);
    }

    /**
     * Returns the schema data
     *
     * @return array
     */
    public function getSchemaData() : array
    {
        return $this->schemaCache;
    }

    /**
     * Gets the data for a bit
     *
     * @param int $bit
     *
     * @return PropertyAnnotationContainer
     */
    public function getDataForBit($bit)
    {
        return new PropertyAnnotationContainer(
            $this->findPropertyInSchema('bit', $bit)
        );
    }

    /**
     * Gets the data for a property
     *
     * @param string $property
     *
     * @return PropertyAnnotationContainer
     */
    public function getDataForProperty($property)
    {
        return new PropertyAnnotationContainer(
            $this->findPropertyInSchema('property', $property)
        );
    }

    /**
     * Parses the cache file data to an array
     *
     * @param string $schemaData the raw cache files contents
     *
     * @return array
     */
    protected function parseSchemaData(string $schemaData) : array
    {
        return json_decode($schemaData, true);
    }

    /**
     * Finds details of an annotation by property.
     *
     * @param $property
     * @param $value
     * @return mixed
     */
    private function findPropertyInSchema($property, $value)
    {
        $annotations = array_filter($this->schemaCache, function($annotation) use ($property, $value) {
            return $annotation[$property] === $value;
        });

        return array_shift($annotations);
    }
}
