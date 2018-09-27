<?php

namespace Consilience\Iso8583\Message\Unpacker;

use Consilience\Iso8583\Cache\CacheManager;
use Consilience\Iso8583\Message\AbstractPackUnpack;
use Consilience\Iso8583\Message\Schema\SchemaManager;
use Consilience\Iso8583\Message\Unpacker\Exception\MessageLengthHeaderException;

/**
 * Class MessageUnpacker
 *
 * @package Consilience\Iso8583\Message\Unpacker
 */
class MessageUnpacker extends AbstractPackUnpack
{
    /** @var SchemaManager $schemaManager the message schema manager */
    protected $schemaManager;

    /** @var CacheManager $cacheManager the schema cache manager */
    protected $cacheManager;

    /** @var bool $encoded indicates whether the message is encoded */
    private $encoded;


    /**
     * MessageUnpacker constructor.
     *
     * @param CacheManager $cacheManager the schema cache manager
     * @param SchemaManager $schemaManager the schema manager class
     * @param bool $encoded whether the message is encoded
     */
    public function __construct(CacheManager $cacheManager, SchemaManager $schemaManager, $encoded)
    {
        $this->cacheManager  = $cacheManager;
        $this->schemaManager = $schemaManager;
        $this->encoded = $encoded;
    }

    /**
     * Parses the message to the schema format
     *
     * @param string $message the iso message, in hexadecimal format
     *
     * @return MessageUnpacker
     *
     * @throws MessageLengthHeaderException if the message fails length validation
     * @throws \Consilience\Iso8583\Cache\Exception\CacheFileNotFoundException
     * @throws \Consilience\Iso8583\Message\Mapper\Exception\MapperNotFoundException
     */
    public function parse(string $message): MessageUnpacker
    {
        // Parse the message length header
        if ($this->getHeaderLength() > 0) {
            $messageLengthHeader = $this->parseMessageLengthHeader($message);
            $shrinkLength = $this->encoded ? $this->getHeaderLength() * 2 : $this->getHeaderLength();
            $this->shrink($message, $shrinkLength);

            $shouldBeLength = $this->encoded ? strlen($message) / 2 : strlen($message);
            if (($messageLengthHeader - $this->getHeaderLength()) != $shouldBeLength) {
                throw new MessageLengthHeaderException(
                    'Message length should be ' . ($messageLengthHeader - $this->getHeaderLength()) . ', but ' .
                    $shouldBeLength . ' was found'
                );
            }
        }

        // Parse the message type indicator
        $this->setMti((string) $this->parseMti($message));
        $this->shrink($message, $this->encoded ? 8 : 4);

        // Parse the bitmap
        $bitmap = $this->parseBitmap($message);

        $numberOfBitmaps = 1;

        if (strlen($bitmap) > 64) {
            $numberOfBitmaps = 2;
        }

        if (strlen($bitmap) > 128) {
            $numberOfBitmaps = 3;
        }

        // Message without bitmaps
        $this->shrink($message, ($numberOfBitmaps * 16));

        // Parse the data element
        $dataElement = $this->parseDataElement($bitmap, $message);

        foreach ($dataElement as $bit => $value) {
            $this->schemaManager
                ->{$this->cacheManager
                    ->getSchemaCache($this->schemaManager->getSchema())
                    ->getDataForBit($bit)
                    ->getSetterName()
                }($value);
        }

        return $this;
    }

    /**
     * Gets the schema manager
     *
     * @return SchemaManager
     */
    public function getSchemaManager(): SchemaManager
    {
        return $this->schemaManager;
    }

    /**
     * Parses the message length header
     *
     * @param string $message
     *
     * @return string the parsed message length header
     */
    protected function parseMessageLengthHeader(string $message): string
    {
        return $this->encoded
            ? base_convert(substr($message, 0, ($this->getHeaderLength() * 2)), 16, 10)
            : substr($message, 0, $this->getHeaderLength());
    }

    /**
     * Parses the message type indicator
     *
     * @param string $message
     *
     * @return string the parsed MTI
     */
    protected function parseMti(string $message): string
    {
        return $this->encoded
            ? hex2bin(substr($message, 0, 8))
            : substr($message, 0, 4);
    }

    /**
     * Parses the bitmap
     *
     * @param string $message
     *
     * @return string the parsed bitmap
     */
    protected function parseBitmap(string $message): string
    {
        $compiledBitmap = "";

        for (;;) {
            // Support for PHPs accuracy issues when using base_convert - ugly, I know!
            $bitmap = implode(null, array_map(function ($bit) {
                return str_pad(base_convert($bit, 16, 2), 8, 0, STR_PAD_LEFT);
            }, str_split(substr($message, 0, 16), 2)));

            $this->shrink($message, 16);

            $compiledBitmap .= $bitmap;

            if (substr($bitmap, 0, 1) !== "1" || strlen($compiledBitmap) > 128) {
                break;
            }
        }

        return $compiledBitmap;
    }

    /**
     * Parses the data element
     *
     * @param string $bitmap the message bitmap
     * @param string $message the message data element
     *
     * @return array the parsed data element
     * @throws \Consilience\Iso8583\Cache\Exception\CacheFileNotFoundException
     * @throws \Consilience\Iso8583\Message\Mapper\Exception\MapperNotFoundException
     */
    protected function parseDataElement(string $bitmap, string $message): array
    {
        $dataElement = [];

        $schemaCache = $this->cacheManager->getSchemaCache($this->schemaManager->getSchema());

        for ($i = 0; $i < strlen($bitmap); $i++) {
            if ($bitmap[$i] === "1") {
                $bit = $i + 1;

                if ($bit === 1 || $bit === 65 || $bit === 129) {
                    continue;
                }

                $bitData = $schemaCache->getDataForBit($bit);

                if ($bitData->isFixedLength()) {
                    $bitReadLength = $this->encoded
                        ? $bitData->getLength() * 2
                        : $bitData->getLength();
                } else {
                    $bitLengthIndicator = $this->encoded
                        ? $bitData->getLengthIndicator() * 2
                        : $bitData->getLengthIndicator();
                    $bitReadLength = $this->encoded
                        ? hex2bin(substr($message, 0, $bitLengthIndicator)) * 2
                        : intval(substr($message, 0, $bitLengthIndicator));

                    $this->shrink($message, $bitLengthIndicator);
                }

                $fieldData = substr($message, 0, $bitReadLength);

                $unpackedBit = $bitData->getMapper()->unpack($fieldData, $this->encoded);

                $this->shrink($message, $bitReadLength);

                $dataElement[$bit] = $unpackedBit;
            }
        }

        return $dataElement;
    }

    /**
     * Shrinks the message
     *
     * @param string $message the message
     * @param int    $length  the length to shrink by
     */
    private function shrink(&$message, $length)
    {
        $message = substr($message, $length);
    }
}
