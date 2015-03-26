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
 * Class FileXmlStream
 * @package MetaSyntactical\Xml\XmlStream
 */
class FileXmlStreamTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that object can be instantiated with valid file path.
     */
    public function testThatObjectCanBeInstantiatedWithValidFilePath()
    {
        new FileXmlStream(__DIR__."/../_data/Xml.xml");
    }

    /**
     * Test that object instantiation fails with no xml file path.
     */
    public function testThatObjectInstantiationFailsWithNoXmlFilePath()
    {
        $this->setExpectedException("InvalidArgumentException", "Stream is no valid xml file.");
        new FileXmlStream(__DIR__."/../_data/NoXml.dat");
    }

    /**
     * Test that object is not in end of file state in the beginning.
     */
    public function testThatObjectIsNotEndOfFileInTheBeginning()
    {
        $object = new FileXmlStream(__DIR__."/../_data/Xml.xml");
        self::assertFalse($object->isEof());
    }

    /**
     * Test that consecutive calls to ::validate() do not modify stream.
     */
    public function testThatConsecutiveCallsToValidateDoNotModifyStream()
    {
        $object = new FileXmlStream(__DIR__."/../_data/Xml.xml");
        $object->validate();
        self::assertEquals("<", $object->read(1));
    }
}
