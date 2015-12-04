<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Command error
   *
   * @package ActiveCollab\Narrative\Narrative\Error
   */
  class CommandError extends Error {

    /**
     * Construct a message
     *
     * @param mixed $command
     * @param string|null $message
     */
    function __construct($command, $message = null) {
      if(empty($message)) {
        $message = 'Command can be a string or an array of command and working directory in which the command should be executed. We got ' . var_export($command, true);
      }

      parent::__construct($message);
    }

  }