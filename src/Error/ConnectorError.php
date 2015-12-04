<?php

namespace ActiveCollab\Narrative\Error;

use Exception;

/**
 * @package ActiveCollab\Narrative\Error
 */
class ConnectorError extends Error
{
    /**
     * @param string         $message
     * @param Exception|null $previous
     */
    function __construct($message = 'Connector error', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
