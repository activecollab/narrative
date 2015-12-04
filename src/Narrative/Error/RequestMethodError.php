<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Invalid request method error
   *
   * @package ActiveCollab\Narrative\Error
   */
  class RequestMethodError extends Error {

    /**
     * Construct exception
     *
     * @param string $method
     * @param string|null $message
     */
    function __construct($method, $message = null) {
      if(empty($message)) {
        $message = "'{}' is not a valid request method";
      }

      parent::__construct($message);
    }

  }