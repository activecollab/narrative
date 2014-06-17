<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Connector error
   *
   * @package ActiveCollab\Narrative\Error
   */
  class ConnectorError extends Error {

    /**
     * Construct a message
     *
     * @param string|null $message
     */
    function __construct($message = null) {
      if(empty($message)) {
        $message = 'Connector error';
      }

      parent::__construct($message);
    }

  }