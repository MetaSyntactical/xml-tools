<?php

/*
 * This file is part of the MetaSyntactical XML Tools package.
 *
 * (c) Daniel Kreuer <d.kreuer@danielkreuer.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reader;

use MetaSyntactical\Xml\Reader\XmlPath;
use PHPUnit_Framework_TestCase;

/**
 * Class XmlPathTest
 * @package Reader
 */
class XmlPathTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that instantiation works with valid paths.
     * @dataProvider provideValidPaths
     * @param string $path
     */
    public function testValidPaths($path)
    {
        new XmlPath($path);
    }

    /**
     * Test that instantiation fails with invalid paths.
     * @dataProvider provideInvalidPaths
     * @param string $path
     */
    public function testInvalidPaths($path)
    {
        $this->setExpectedException("InvalidArgumentException", "does not match expression.");
        new XmlPath($path);
    }

    /**
     * Provide valid paths.
     * @return string[]
     */
    public function provideValidPaths()
    {
        return [
            ["/foo"],
            ["/foo/bar"],
            ["/foo/bar/baz"],
            ["/foo/@quux"],
            ["/foo/bar/@quux"],
            ["/foo/b123"],
            ["/foo/b123/@a123"],
            ["/foo/b123/@abc"],
            ["/f-o-o/b-123/@a-3"],
            ["/a_b_c/@a_b-c"],
        ];
    }

    /**
     * Provide invalid paths.
     * @return string[]
     */
    public function provideInvalidPaths()
    {
        return [
            ["/foo::siblings/bar"],
            ["/foo[/bar]"],
            ["/foo/@bar/baz"],
        ];
    }
}
