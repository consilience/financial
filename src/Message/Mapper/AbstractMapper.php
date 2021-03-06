<?php

namespace Consilience\Iso8583\Message\Mapper;

use Consilience\Iso8583\Container\PropertyAnnotationContainer;
use DateTime;

abstract class AbstractMapper implements MapperInterface
{
    /** @var PropertyAnnotationContainer $propertyAnnotationContainer the property annotation data container */
    protected $propertyAnnotationContainer;

    /**
     * Constructor.
     *
     * @param PropertyAnnotationContainer $propertyAnnotationContainer the property annotation container
     */
    public function __construct(PropertyAnnotationContainer $propertyAnnotationContainer)
    {
        $this->propertyAnnotationContainer = $propertyAnnotationContainer;
    }

    /**
     * @inheritdoc
     */
    public function pack(string $data, $encoded) : string
    {
        $packedField = $encoded ? bin2hex($data) : $data;

        if (!$this->propertyAnnotationContainer->isFixedLength()) {
            $paddingLength = $encoded ? strlen($packedField) / 2 : strlen($packedField);

            $lengthIndicator = sprintf(
                '%0' . $this->propertyAnnotationContainer->getLengthIndicator() . 'd',
                $paddingLength
            );

            $variableFieldHeaderLength = $encoded
                ? bin2hex($lengthIndicator)
                : $lengthIndicator;

            return $variableFieldHeaderLength . $packedField;
        }

        return $packedField;
    }

    /**
     * @inheritdoc
     */
    public function unpack(string $data, $encoded)
    {
        $parsedData = $encoded ? hex2bin($data) : $data;

        if ('DateTime' == $this->propertyAnnotationContainer->getType()) {
            $parsedData = DateTime::createFromFormat($this->propertyAnnotationContainer->getFormat(), $parsedData);
        }

        return $parsedData;
    }
}
