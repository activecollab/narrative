<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Validator not found error
   *
   * @package ActiveCollab\Narrative\Narrative\Error
   */
  class NoValidatorError extends Error {

    /**
     * @param string $validator
     * @param null $message
     */
    function __construct($validator, $message = null) {
      if(empty($message)) {
        $message = "Validator '{$validator}' not found";
      }

      parent::__construct($message);
    }

  }