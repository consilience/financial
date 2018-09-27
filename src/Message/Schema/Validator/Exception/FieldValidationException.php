<?php

namespace Consilience\Iso8583\Message\Schema\Validator\Exception;

use Exception;
use Consilience\Iso8583\Container\PropertyAnnotationContainer;

/**
 * Thrown when the schema field validation fails
 *
 * Class FieldValidationException
 *
 * @package Consilience\Iso8583\Message\Schema\Validator\Exception
 */
class FieldValidationException extends Exception
{

    /** @var PropertyAnnotationContainer $propertyAnnotationContainer the annotation container (validation rules) */
    protected $propertyAnnotationContainer;

    /** @var mixed $data the data that failed validation */
    protected $data;

    /**
     * Gets the property annotation container
     *
     * @return PropertyAnnotationContainer
     */
    public function getPropertyAnnotationContainer(): PropertyAnnotationContainer
    {
        return $this->propertyAnnotationContainer;
    }

    /**
     * Sets the property annotation container
     *
     * @param PropertyAnnotationContainer $propertyAnnotationContainer
     *
     * @return FieldValidationException
     */
    public function setPropertyAnnotationContainer(
        PropertyAnnotationContainer $propertyAnnotationContainer
    ): FieldValidationException {
        $this->propertyAnnotationContainer = $propertyAnnotationContainer;

        return $this;
    }

    /**
     * Gets the data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data
     *
     * @param mixed $data
     *
     * @return FieldValidationException
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
