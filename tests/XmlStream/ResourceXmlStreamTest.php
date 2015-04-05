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

use PHPUnit_Framework_TestCase;

/**
 * Class ResourceXmlStreamTest
 * @package MetaSyntactical\Xml\Reader
 */
class ResourceXmlStreamTest extends PHPUnit_Framework_TestCase
{
    private $socket;

    /**
     * Tear down.
     */
    public function tearDown()
    {
        if (is_resource($this->socket)) {
            socket_close($this->socket);
        }
    }

    /**
     * Test that object can be instantiated with valid resource.
     */
    public function testThatObjectCanBeInstantiatedWithValidResource()
    {
        $resource = fopen(__DIR__."/../_data/Xml.xml", "r");
        new ResourceXmlStream($resource);
    }

    /**
     * Test that object won't be instantiated with not readable resource.
     */
    public function testThatObjectWontBeInstantiatedWithNotReadableResource()
    {
        $this->setExpectedException("InvalidArgumentException", "to be a readable stream");
        $resource = fopen(__DIR__."/../_data/Xml.xml", "a+");
        new ResourceXmlStream($resource);
    }

    /**
     * Test that object won't be instantiated with resource which is no stream.
     */
    public function testThatObjectWontBeInstantiatedWithResourceWhichIsNoStream()
    {
        $this->setExpectedException("InvalidArgumentException", "to be a readable stream");
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, getprotobyname('udp'));
        new ResourceXmlStream($this->socket);
    }

    /**
     * Test that object is not in end of file state in the beginning.
     */
    public function testThatObjectIsNotEndOfFileInTheBeginning()
    {
        $object = new ResourceXmlStream(fopen(__DIR__."/../_data/Xml.xml", "rb"));
        self::assertFalse($object->isEof());
    }
}
