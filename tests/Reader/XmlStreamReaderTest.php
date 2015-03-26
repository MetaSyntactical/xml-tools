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

use DOMElement;
use MetaSyntactical\Xml\Tests\LoggerProphecy;
use MetaSyntactical\Xml\XmlStream\FileXmlStream;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;

/**
 * Class XmlStreamReaderTest
 * @package MetaSyntactical\Xml\Reader
 */
class XmlStreamReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that stream reader without registered callbacks does nothing.
     */
    public function testThatStreamReaderWithoutCallbacksDoesNothing()
    {
        $logger = new LoggerProphecy();

        $object = new XmlStreamReader();
        $object->setLogger($logger);

        $object->parse(new FileXmlStream(__DIR__."/../_data/Xml.xml"));

        self::assertEmpty($logger->getMessages());
    }

    /**
     * Test tjat stream reader parses on registered callback.
     */
    public function testThatStreamReaderParsesOnRegisteredCallback()
    {
        $logger = new LoggerProphecy();

        $object = new XmlStreamReader();
        $object->setLogger($logger);

        $object->registerCallback(new XmlPath("/not/existing/path"), function () {

        });

        $object->parse(new FileXmlStream(__DIR__."/../_data/Xml.xml"));

        $expected = [
            [
                "level"   => "debug",
                "message" => "method entry",
                "context" => [
                    "method" => "initializeInternalParserVariables",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method exit",
                "context" => [
                    "method" => "initializeInternalParserVariables",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method entry",
                "context" => [
                    "method" => "initializeParser",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method exit",
                "context" => [
                    "method" => "initializeParser",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method entry",
                "context" => [
                    "method" => "runParser",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method entry",
                "context" => [
                    "method" => "parseNodeStart",
                    "tag"    => "dummy",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method entry",
                "context" => [
                    "method" => "addData",
                    "tag"    => "dummy",
                    "data"   => "<dummy>",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method exit",
                "context" => [
                    "method"       => "addData",
                    "current_path" => "/dummy",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method exit",
                "context" => [
                    "method"       => "parseNodeStart",
                    "current_path" => "/dummy",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method entry",
                "context" => [
                    "method" => "parseNodeEnd",
                    "tag"    => "dummy",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method entry",
                "context" => [
                    "method" => "addData",
                    "tag"    => "dummy",
                    "data"   => "</dummy>",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method exit",
                "context" => [
                    "method"       => "addData",
                    "current_path" => "/dummy",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method exit",
                "context" => [
                    "method"       => "parseNodeEnd",
                    "current_path" => "",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method exit",
                "context" => [
                    "method" => "runParser",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method entry",
                "context" => [
                    "method" => "terminateParser",
                ],
            ],
            [
                "level"   => "debug",
                "message" => "method exit",
                "context" => [
                    "method" => "terminateParser",
                ],
            ],
        ];
        self::assertEquals($expected, $logger->getMessages());
    }

    /**
     * Test parsing invokes callback on root node.
     */
    public function testParsingInvokesCallbackOnRootNode()
    {
        $callbackInvoked = false;

        $object = new XmlStreamReader();

        $object->registerCallback(new XmlPath("/dummy"), function (DOMElement $node) use (&$callbackInvoked) {
            $callbackInvoked = true;
        });

        $object->parse(new FileXmlStream(__DIR__."/../_data/Xml.xml"));

        self::assertTrue($callbackInvoked);
    }
}
