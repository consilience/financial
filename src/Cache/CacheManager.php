<?php

namespace Consilience\Iso8583\Cache;

use Consilience\Iso8583\Cache\Exception\CacheConfigurationException;
use Consilience\Iso8583\Cache\Exception\CacheFileNotFoundException;
use Consilience\Iso8583\Cache\Exception\CacheWriterException;
use Consilience\Iso8583\Message\Schema\MessageSchemaInterface;
use ReflectionClass;
use zpt\anno\Annotations;

/**
 *  Class CacheManager
 *
 * @package Consilience\Iso8583\Cache
 */
class CacheManager
{
    // The message schema cache file name
    const CACHED_SCHEMA_FILE_NAME = 'schema.json';

    /** @var array $config the cache manager default configuration */
    protected $config = [
        'cacheDirectory' => __DIR__ . '/../../cache',
    ];

    /**
     * CacheManager constructor
     *
     * @param array $configuration the cache manager configuration
     */
    public function __construct($configuration = [])
    {
        $this->setConfiguration($configuration);
    }

    /**
     * Generates the schema cache
     *
     * @param MessageSchemaInterface $schemaClass the class to generate schema for
     *
     * @return CacheFile|false CacheFile, or false if not found
     *
     * @throws CacheWriterException if the cache file cannot be written
     */
    public function generateSchemaCache(MessageSchemaInterface $schemaClass)
    {
        $reflectedSchema = new ReflectionClass($schemaClass);

        $schemaPropertyAnnotations = [];
        foreach ($reflectedSchema->getProperties() as $property) {
            $propertyAnnotations             = (new Annotations($property))->asArray();
            $propertyAnnotations['property'] = $property->name;

            $schemaPropertyAnnotations[] = $propertyAnnotations;
        }

        $cachePath = $this->getConfiguration('cacheDirectory') . DIRECTORY_SEPARATOR . $schemaClass->getName();

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        if (!file_put_contents(
            $cachePath . DIRECTORY_SEPARATOR . self::CACHED_SCHEMA_FILE_NAME,
            json_encode($schemaPropertyAnnotations)
        )) {
            throw new CacheWriterException('Cannot write cache file: ' . $cachePath);
        }

        return $this->getSchemaCache($schemaClass);
    }

    /**
     * Gets the message schema
     *
     * @param MessageSchemaInterface $schemaClass the schema cache
     *
     * @return CacheFile|false CacheFile, or false if not found
     *
     * @throws CacheFileNotFoundException if the cache file cannot be found
     */
    public function getSchemaCache(MessageSchemaInterface $schemaClass)
    {
        $cacheFilePath = $this->getConfiguration('cacheDirectory') . DIRECTORY_SEPARATOR .
            $schemaClass->getName() . DIRECTORY_SEPARATOR . self::CACHED_SCHEMA_FILE_NAME;

        if (!file_exists($cacheFilePath)) {
            $this->generateSchemaCache($schemaClass);
        }

        if (is_readable($cacheFilePath)) {
            return new CacheFile(file_get_contents($cacheFilePath));
        }

        throw new CacheFileNotFoundException('Cache file not found for ' . $schemaClass->getName());
    }

    /**
     * Sets the cache manager configuration
     *
     * @param $configuration array the cache manager configuration
     */
    protected function setConfiguration(array $configuration)
    {
        $this->config = array_merge($this->config, $configuration);
    }

    /**
     * Gets a configuration item
     *
     * @param string $key the configuration key
     *
     * @return mixed the configuration item
     *
     * @throws CacheConfigurationException if the configuration item cannot be found
     */
    protected function getConfiguration($key)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        throw new CacheConfigurationException('Configuration key ' . $key . 'doesn\'t exist');
    }
}
