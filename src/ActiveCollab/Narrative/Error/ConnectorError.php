<?php

  namespace ActiveCollab\Narrative\Error;

  use Exception;

  /**
   * Connector error
   *
   * @package ActiveCollab\Narrative\Error
   */
  class ConnectorError extends Error
  {
    /**
     * Construct a message
     *
     * @param string|null $message
     * @param Exception|null $previous
     */
    function __construct($message = null, Exception $previous = null) {
      if(empty($message)) {
        $message = 'Connector error';
      }

      parent::__construct($message, 0, $previous);
    }
  }