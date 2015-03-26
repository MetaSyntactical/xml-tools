<?php

/*
 * This file is part of the MetaSyntactical XML Tools package.
 *
 * (c) Daniel Kreuer <d.kreuer@danielkreuer.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MetaSyntactical\Xml\Tests;

use Psr\Log\AbstractLogger;

/**
 * Logger Prophecy used in tests to monitor logged messages.
 *
 * @package MetaSyntactical\Xml\Tests
 */
class LoggerProphecy extends AbstractLogger
{
    private $messages = [];

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $this->messages[] = [
            "level"   => $level,
            "message" => $message,
            "context" => $context,
        ];
    }

    /**
     * @return mixed[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
