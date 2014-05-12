<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Validator not found error
   *
   * @package ActiveCollab\Narrative\Error
   */
  class ValidatorParamsError extends Error {

    /**
     * @param string $validator
     * @param null $message
     */
    function __construct($validator, $message = null) {
      if(empty($message)) {
        $message = "Validator '{$validator}' params are not valid";
      }

      parent::__construct($message);
    }

  }