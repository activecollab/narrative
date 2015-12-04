<?php

  namespace ActiveCollab\Narrative\Error;

  /**
   * Exception that is thrown when we find an error in JSON bit of .narr file
   *
   * @package ActiveCollab\Narrative\Narrative\Error
   */
  class ParseJsonError extends Error {

    /**
     * Problematic JSON code
     *
     * @var string
     */
    private $json;

    /**
     * Parse error code
     *
     * @var integer
     */
    private $json_error;

    /**
     * Construct exception instance
     *
     * @param string $json
     * @param int $json_error
     * @param string|null $message
     */
    function __construct($json, $json_error, $message = null)
    {
      $this->json = $json;
      $this->json_error = $json_error;

      if(empty($message)) {
        switch($json_error) {
          case JSON_ERROR_DEPTH:
            $message = 'The maximum stack depth has been exceeded'; break;
          case JSON_ERROR_STATE_MISMATCH:
            $message = 'Invalid or malformed JSON'; break;
          case JSON_ERROR_CTRL_CHAR:
            $message = 'Control character error, possibly incorrectly encoded'; break;
          case JSON_ERROR_SYNTAX:
            $message = 'Syntax error'; break;
          case JSON_ERROR_UTF8:
            $message = 'Malformed UTF-8 characters, possibly incorrectly encoded'; break;
          default:
            if(defined('JSON_ERROR_RECURSION') && $json_error === JSON_ERROR_RECURSION) {
              $message = 'One or more recursive references in the value to be encoded';
            } elseif(defined('JSON_ERROR_INF_OR_NAN') && $json_error === JSON_ERROR_INF_OR_NAN) {
              $message = 'One or more NAN or INF values in the value to be encoded';
            } elseif(defined('JSON_ERROR_UNSUPPORTED_TYPE') && $json_error === JSON_ERROR_UNSUPPORTED_TYPE) {
              $message = 'A value of a type that cannot be encoded was given';
            } else {
              $message = 'Failed to parse JSON';
            }
        }
      }

      parent::__construct($message);
    }

    /**
     * Return JSON source
     *
     * @return string
     */
    function getJson()
    {
      return $this->json;
    }

    /**
     * Return JSOn error code
     *
     * @return int
     */
    function getJsonError()
    {
      return $this->json_error;
    }

  }