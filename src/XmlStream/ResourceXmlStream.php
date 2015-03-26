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
use Assert\InvalidArgumentException;

/**
 * Class ResourceXmlStream
 * @package MetaSyntactical\Xml\XmlStream
 */
final class ResourceXmlStream extends AbstractXmlStream
{
    /**
     * @param resource $resource readable stream resource
     */
    public function __construct($resource)
    {
        $streamInfo = stream_get_meta_data($resource);
        if (stristr($streamInfo["mode"], "r") === false) {
            $message = 'Stream "<RESOURCE>" was expected to be a readable stream resource.';

            throw new InvalidArgumentException($message, Assertion::INVALID_READABLE, null, $resource);
        }

        $this->setResource($resource);
        $this->validate();
    }
}
