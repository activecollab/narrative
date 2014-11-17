<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Exception that is thrown when temp folder is not found
   *
   * @package ActiveCollab\Narrative\Error
   */
  class TempNotFoundError extends Error
  {
    /**
     * @param string $expected_location
     * @param string|null $message
     */
    function __construct($expected_location, $message = null)
    {
      if (empty($message)) {
        $message = "Temp folder not found at '$expected_location'";
      }

      parent::__construct($message);
    }
  }