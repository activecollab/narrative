<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Validation error
   *
   * @package ActiveCollab\Narrative\Narrative\Error
   */
  class ValidationError extends Error {

    /**
     * @param string $validator
     * @param null $message
     */
    function __construct($validator, $message = null) {
      if(empty($message)) {
        $message = "Result validation failed. Validator '{$validator}' returned false";
      }

      parent::__construct($message);
    }

  }