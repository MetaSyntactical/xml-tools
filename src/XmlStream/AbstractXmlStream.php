<?php

/*
 * This file is part of the MetaSyntactical XML Tools package.
 *
 * (c) Daniel Kreuer <d.kreuer@danielkreuer.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MetaSyntactical\Xml\XmlStream;

use Assert\Assertion;

/**
 * Class XmlStream
 * @package MetaSyntactical\Xml\XmlStream
 */
abstract class AbstractXmlStream
{
    const DEFAULT_CHUNK_SIZE = 1048676;
    /**
     * @var resource
     */
    private $resource;

    /**
     * @return $this
     */
    public function rewind()
    {
        rewind($this->resource);

        return $this;
    }

    /**
     * @param int $chunkSize
     * @return string
     */
    public function read($chunkSize = self::DEFAULT_CHUNK_SIZE)
    {
        Assertion::integerish($chunkSize);

        return fread($this->resource, $chunkSize);
    }

    /**
     * @return bool
     */
    public function isEof()
    {
        return feof($this->resource);
    }

    /**
     * @return $this
     */
    public function validate()
    {
        $this->rewind();
        $firstBytes = fread($this->resource, 1);
        $this->rewind();

        Assertion::eq($firstBytes, "<", "Stream is no valid xml file. Does not start with '<'.");

        return $this;
    }

    /**
     * @internal
     * @param resource $resource readable stream resource
     */
    final protected function setResource($resource)
    {
        $this->resource = $resource;
    }
}
