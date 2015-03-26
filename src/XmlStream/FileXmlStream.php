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
 * Class FileXmlStream
 * @package MetaSyntactical\Xml\XmlStream
 */
final class FileXmlStream extends AbstractXmlStream
{
    /**
     * @param string $path
     */
    public function __construct($path)
    {
        Assertion::readable($path);

        $this->setResource(fopen($path, "rb"));
        $this->validate();
    }
}
