<?php

/*
 * This file is part of the MetaSyntactical XML Tools package.
 *
 * (c) Daniel Kreuer <d.kreuer@danielkreuer.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MetaSyntactical\Xml\Reader;

use Assert\Assertion;

/**
 * Class XPath
 * @package MetaSyntactical\Xml\Reader
 */
class XmlPath
{
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->setPath($path);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    private function setPath($path)
    {
        Assertion::string($path);
        Assertion::regex($path, '|(?mi-Us)^(/[a-zA-Z][a-zA-Z0-9_-]*)+(/@[a-zA-Z][a-zA-Z0-9_-]*)?$|');

        $this->path = $path;

        return $this;
    }
}
