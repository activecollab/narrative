<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Exception that is thrown when required param is not provided in a template function call
   *
   * @package ActiveCollab\Narrative\Error
   */
  class ParamRequiredError extends Error
  {
    /**
     * @param string $name
     * @param string|null $message
     */
    function __construct($name, $message = null)
    {
      if (empty($message)) {
        $message = "Param '$name' is required";
      }

      parent::__construct($message);
    }
  }