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

use DOMDocument;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleXMLElement;
use SplObjectStorage;
use MetaSyntactical\Xml\XmlStream\AbstractXmlStream;

/**
 * Class XmlStreamReader
 * @package MetaSyntactical\Xml\Reader
 */
class XmlStreamReader implements LoggerAwareInterface
{
    /**
     * Used to indicate in a registered callback to skip further processing
     * of the currently matched path.
     */
    const STOP_PATH_PROCESSING = 0x1001;

    /**
     * Used to indicate in a registered callback to skip further processing
     * of the currently processed xml stream.
     */
    const STOP_FILE_PROCESSING = 0x1002;

    /**
     * @var SplObjectStorage|SplObjectStorage[]
     */
    private $callbacks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    // ------------------------------------------------------------------------
    //  Xml Parser internal properties

    /**
     * @var resource
     */
    private $parser;

    /**
     * @var bool
     */
    private $parserRunning = false;

    /**
     * @var string[]
     */
    private $currentPath;

    /**
     * @var string[]
     */
    private $parsedNamespaces;

    /**
     * @var string[]
     */
    private $pathData;


    /**
     *
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * Sets a logger instance on the object.
     *
     * This allows to monitor the internal xml parser flow. The public API
     * won't log any messages itself and one is advised to use AOP
     * mechanisms to log the calls to the public API, e.g.
     *   lisachenko/go-aop-php
     *
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Allows to match on a specific xml path.
     *
     * The provided callback will be called with several parameters. A possible
     * signature might look like:
     *
     * function (DOMNode $element);
     *
     * The callback might return self::STOP_PATH_PROCESSING to indicate that
     * the currently matched path should not be processed any further (skipping
     * other added callbacks on this path).
     * The callback might return self::STOP_FILE_PROCESSING to indicate that
     * no further processing of the currently processed xml stream should take
     * place.
     *
     * @param XmlPath  $path
     * @param callable $callback
     * @return $this
     */
    final public function registerCallback(XmlPath $path, callable $callback)
    {
        if (!is_a($this->callbacks, SplObjectStorage::class)) {
            $this->callbacks = new SplObjectStorage();
        }
        if (!isset($this->callbacks[$path])) {
            $this->callbacks[$path] = new SplObjectStorage();
        }
        if (!$this->callbacks[$path]->contains($callback)) {
            $this->callbacks[$path]->attach($callback);
        }

        return $this;
    }

    /**
     * @param callable $callback
     * @param XmlPath  $path
     * @return $this|XmlStreamReader
     */
    final public function deregisterCallback(callable $callback, XmlPath $path = null)
    {
        if (!is_a($this->callbacks, SplObjectStorage::class)) {
            $this->callbacks = new SplObjectStorage();
        }
        if (is_null($path)) {
            return $this->deregisterCallbackInPath($path, $callback);
        }
        foreach ($this->callbacks as $path) {
            $this->deregisterCallbackInPath($path, $callback);
        }

        return $this;
    }

    /**
     * @param XmlPath $path
     * @return $this
     */
    final public function deregisterPath(XmlPath $path)
    {
        if (!is_a($this->callbacks, SplObjectStorage::class)) {
            $this->callbacks = new SplObjectStorage();
        }

        if (isset($this->callbacks[$path])) {
            unset($this->callbacks[$path]);
        }

        return $this;
    }

    /**
     * @param AbstractXmlStream $stream
     */
    final public function parse(AbstractXmlStream $stream)
    {
        if (!is_a($this->callbacks, SplObjectStorage::class)) {
            $this->callbacks = new SplObjectStorage();
        }

        if (!count($this->callbacks)) {
            return;
        }

        try {
            $this->initializeInternalParserVariables();
            $this->initializeParser();
            $this->runParser($stream);
        } finally {
            $this->terminateParser();
        }
    }

    /**
     * @param XmlPath  $path
     * @param callable $callback
     * @return $this
     */
    private function deregisterCallbackInPath(XmlPath $path, callable $callback)
    {
        if (!isset($this->callbacks[$path])) {
            return $this;
        }

        if ($this->callbacks[$path]->contains($callback)) {
            $this->callbacks[$path]->detach($callback);
        }

        return $this;
    }

    // ------------------------------------------------------------------------
    //  Xml Parser internal methods

    private function initializeInternalParserVariables()
    {
        $this->logger->debug("method entry", ["method" => __FUNCTION__]);

        $this->parserRunning = false;
        $this->currentPath = [""];
        $this->parsedNamespaces = [];
        $this->pathData = [];

        $this->logger->debug("method exit", ["method" => __FUNCTION__]);
    }

    private function initializeParser()
    {
        $this->logger->debug("method entry", ["method" => __FUNCTION__]);

        $this->parser = xml_parser_create();
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "parseNodeStart", "parseNodeEnd");
        xml_set_character_data_handler($this->parser, "addCData");
        xml_set_default_handler($this->parser, "addData");

        $this->logger->debug("method exit", ["method" => __FUNCTION__]);
    }

    private function runParser(AbstractXmlStream $stream)
    {
        $this->logger->debug("method entry", ["method" => __FUNCTION__]);

        $this->parserRunning = true;

        while ($this->parserRunning && $data = $stream->read()) {
            if (!xml_parse($this->parser, $data, $stream->isEof())) {
                throw new \RuntimeException(
                    sprintf(
                        "%s At line: %s",
                        xml_error_string(xml_get_error_code($this->parser)),
                        xml_get_current_line_number($this->parser)
                    )
                );
            }
        }

        $this->logger->debug("method exit", ["method" => __FUNCTION__]);
    }

    private function terminateParser()
    {
        $this->logger->debug("method entry", ["method" => __FUNCTION__]);

        xml_parser_free($this->parser);
        $this->parser = null;

        $this->logger->debug("method exit", ["method" => __FUNCTION__]);
    }

    private function parseNodeStart($parser, $tag, $attributes)
    {
        $this->logger->debug("method entry", ["method" => __FUNCTION__, "tag" => $tag]);

        array_push($this->currentPath, "$tag");

        $stopParsing = $this->fireAttributeCallbacks($attributes) === false;

        foreach ($this->callbacks as $path) {
            /** @var XmlPath $path */
            if ($path->getPath() !== $this->getCurrentPath()) {
                continue;
            }

            if (!isset($this->pathData[$this->getCurrentPath()])) {
                $this->pathData[$this->getCurrentPath()] = "";
            }
        }

        $data = "<".$tag;
        foreach ($attributes as $key => $value) {
            $options = ENT_QUOTES | ENT_XML1;

            $value = htmlentities($value, $options, "UTF-8");
            $data .= " $key=\"$value\"";

            if (stripos($key, "xmlns:") !== false) {
                $namespacePrefix = str_replace("xmlns", "", $key);
                $this->parsedNamespaces[$namespacePrefix] = $value;
            }
        }
        $data .= ">";
        $this->addData($parser, $data);

        $this->logger->debug("method exit", ["method" => __FUNCTION__, "current_path" => $this->getCurrentPath()]);
    }

    private function parseNodeEnd($parser, $tag)
    {
        $this->logger->debug("method entry", ["method" => __FUNCTION__, "tag" => $tag]);

        $data = "</$tag>";
        $this->addData($parser, $data);

        foreach ($this->callbacks as $path) {
            /** @var XmlPath $path */
            if ($path->getPath() !== $this->getCurrentPath()) {
                continue;
            }

            $payloadElement = $this->createElementPayload($this->getCurrentPath());

            foreach ($this->callbacks[$path] as $callback) {
                /** @var callable $callback */
                $returnVal = $callback($payloadElement);
                switch ($returnVal) {
                    case self::STOP_PATH_PROCESSING:
                        break(2);

                    case self::STOP_FILE_PROCESSING:
                        break(3);
                }
            }
        }

        unset($this->pathData[$this->getCurrentPath()]);
        array_pop($this->currentPath);

        $this->logger->debug("method exit", ["method" => __FUNCTION__, "current_path" => $this->getCurrentPath()]);
    }

    private function addCData($parser, $data)
    {
        $this->logger->debug("method entry", ["method" => __FUNCTION__, "tag" => end($this->currentPath), "data" => $data]);

        $data = implode("]]]]><![CDATA[", explode("]]>", $data));
        $this->addCData($parser, "<![CDATA[$data]]>");

        $this->logger->debug("method exit", ["method" => __FUNCTION__, "current_path" => $this->getCurrentPath()]);
    }

    private function addData($parser, $data)
    {
        $this->logger->debug("method entry", ["method" => __FUNCTION__, "tag" => end($this->currentPath), "data" => $data]);

        foreach ($this->pathData as $key => $val) {
            if (strpos($this->getCurrentPath(), $key) !== false) {
                $this->pathData[$key] .= $data;
            }
        }

        $this->logger->debug("method exit", ["method" => __FUNCTION__, "current_path" => $this->getCurrentPath()]);
    }

    private function getCurrentPath()
    {
        return implode("/", $this->currentPath);
    }

    private function fireAttributeCallbacks($attributes)
    {
        foreach ($attributes as $key => $value) {
            $attributePath = $this->getCurrentPath()."@$key";

            foreach ($this->callbacks as $path) {
                /** @var XmlPath $path */
                if ($path->getPath() !== $attributePath) {
                    continue;
                }

                $xmlAttribute = $this->createAttributePayload($key, $value);

                foreach ($this->callbacks[$path] as $callback) {
                    /** @var callable $callback */
                    $returnVal = $callback($xmlAttribute);
                    switch ($returnVal) {
                        case self::STOP_PATH_PROCESSING:
                            break(2);

                        case self::STOP_FILE_PROCESSING:
                            return false;
                    }
                }
            }
        }
    }

    private function createAttributePayload($attributeName, $attributeValue)
    {
        $document = new DOMDocument();
        $attribute = $document->createAttribute($attributeName);
        $attribute->nodeValue = $attributeValue;

        return $attribute;
    }

    private function createElementPayload($path)
    {
        $rootNamespaceDefinition = "";
        $namespaces = $this->parsedNamespaces;
        $matches = [];
        $pathData = $this->pathData[$path];
        $regex = "(xmlns:(?P<namespace>[^=]+)=\"[^\\\"]+\")sm";

        if (preg_match_all($regex, $pathData, $matches)) {
            foreach ($matches["namespace"] as $key => $namespacePrefix) {
                if (!isset($namespaces[$namespacePrefix])) {
                    continue;
                }
                unset($namespaces[$namespacePrefix]);
            }
        }

        foreach ($namespaces as $namespacePrefix => $namespaceUri) {
            $rootNamespaceDefinition .= " xmlns:$namespacePrefix=\"$namespaceUri\"";
        }

        $payloadElement = new SimpleXMLElement(
            preg_replace("(^(<[^\\s>]+))", '$1'.$rootNamespaceDefinition, $pathData),
            LIBXML_COMPACT | LIBXML_NOERROR | LIBXML_NOWARNING
        );

        return dom_import_simplexml($payloadElement);
    }
}
