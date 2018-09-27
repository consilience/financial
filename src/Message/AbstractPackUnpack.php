<?php

namespace Consilience\Iso8583\Message;

use Consilience\Iso8583\Message\Schema\MessageTypeIndicator;

/**
 * Class AbstractPackUnpack
 *
 * @package Consilience\Iso8583\Message
 */
abstract class AbstractPackUnpack
{

    /** @var int $headerLength the message header length */
    protected $headerLength;

    /** @var string $mti the message type indicator */
    protected $mti;

    /**
     * Sets the message header length
     *
     * @param int $headerLength
     *
     * @return AbstractPackUnpack
     */
    public function setHeaderLength(int $headerLength): AbstractPackUnpack
    {
        $this->headerLength = $headerLength;

        return $this;
    }

    /**
     * Gets the message header length
     *
     * @return int
     */
    public function getHeaderLength(): int
    {
        return $this->headerLength;
    }

    /**
     * Sets the message type indicator
     *
     * @param string $mti
     *
     * @return AbstractPackUnpack
     */
    public function setMti(string $mti): AbstractPackUnpack
    {
        $this->mti = $mti;

        return $this;
    }

    /**
     * Gets the message type indicator
     *
     * @return MessageTypeIndicator
     */
    public function getMti(): MessageTypeIndicator
    {
        return new MessageTypeIndicator($this->mti);
    }
}
