<?php

  namespace ActiveCollab\Narrative\StoryElement;

  use ActiveCollab\Narrative\Error\NoValidatorError;
  use ActiveCollab\Narrative\Error\ValidatorParamsError;
  use ActiveCollab\Narrative\Error\ParseJsonError;
  use ActiveCollab\Narrative\Error\RequestMethodError;
  use ActiveCollab\SDK\Exceptions\AppException;
  use ActiveCollab\SDK\Exceptions\CallFailed;
  use ActiveCollab\SDK\Response;
  use Symfony\Component\Console\Output\OutputInterface;
  use Peekmo\JsonPath\JsonStore;

  use ActiveCollab\SDK\Client as API;

  /**
   * HTTP request element
   *
   * @package ActiveCollab\Narrative\StoryElement
   */
  class Request extends StoryElement {

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    /**
     * Request method
     *
     * @var string
     */
    private $method;

    /**
     * Element source code
     *
     * @var string|array
     */
    protected $source = "{";

    /**
     * @param string $method
     */
    function __construct($method) {
      $this->method = $method;
    }

    /**
     * Execute the command
     *
     * @param OutputInterface $output
     * @return array
     */
    function execute($output) {
      API::setKey('1-TESTTESTTESTTESTTESTTESTTESTTESTTESTTEST');
      API::setUrl('http://activecollab.dev/api.php');

      $response = $request_time = null;

      try {
        switch($this->getMethod()) {
          case self::GET:
            $response = API::get($this->getPath()); break;
          case self::POST:
            $response = API::post($this->getPath(), $this->getPayload(), $this->getAttachments()); break;
          case self::PUT:
            $response = API::put($this->getPath(), $this->getPayload(), $this->getAttachments()); break;
          case self::DELETE:
            $response = API::delete($this->getPath(), $this->getPayload()); break;
          default:
            throw new RequestMethodError($this->getMethod());
        }
      } catch(\Exception $e) {
        if(method_exists($e, 'getHttpCode') && $e->getHttpCode() === 404) {
          $response = 404;
          $request_time = method_exists($e, 'getRequestTime') ? $e->getRequestTime() : null;
        } else {
          $output->writeln('<error>Failed to execute ' . $this->getPath() . '. Reason: ' . $e->getMessage() . '</error>');
        }
      }

      if($response) {
        $passes = $failures = [];

        $this->validate($response, $passes, $failures);
        $this->printRequestStatusLine($response, $passes, $failures, $request_time, $output);

        return [ $response, $passes, $failures ];
      } else {
        return [ null, null, null ];
      }
    }

    /**
     * Output status message
     *
     * @param Response|int $response
     * @param array $passes
     * @param array $failures
     * @param float|null $request_time
     * @param OutputInterface $output
     */
    private function printRequestStatusLine($response, $passes, $failures, $request_time, OutputInterface $output) {
      if(empty($failures)) {
        $color = 'info';
      } else {
        $color = 'error';
        $output->writeln('');
      }

      $http_code = $response instanceof Response ? $response->getHttpCode() : (integer) $response;
      $request_time = $response instanceof Response ? $response->getTotalTime() : (float) $request_time;
      $prep = $this->isPreparation() ? ' <question>[PREP]</question>' : '';

      $output->writeln("<$color>" . $this->getMethod() . ' ' . $this->getPath() . " - {$http_code} in {$request_time} seconds</$color> {$prep}");

      // Output failure lines
      if(count($failures)) {
        foreach($failures as $failure) {
          $output->writeln('<error>- ' . $failure . '</error>');
        }
        $output->writeln('');
      }

      // Output response, if needed
      if(isset($this->source['dump_response']) && $this->source['dump_response'] && $response instanceof Response) {
        if($response->isJson()) {
          print_r($response->getJson());
        } else {
          $output->writeln($response->getBody());
        }
      }
    }

    // ---------------------------------------------------
    //  Validator
    // ---------------------------------------------------

    /**
     * Validate response
     *
     * @param Response|int $response
     * @param array $passes
     * @param array $failures
     * @return boolean
     */
    private function validate(&$response, array &$passes, array &$failures) {
      if(isset($this->source['validate']) && is_array($this->source['validate'])) {
        foreach($this->source['validate'] as $validator => $validator_data) {
          $this->callValidator($validator, $validator_data, $response, $passes, $failures);
        }
      }

      return empty($failures);
    }

    /**
     * Return validator method name
     *
     * @param string $validator
     * @param mixed $validator_data
     * @param Response|int $response
     * @param array $passes
     * @param array $failures
     * @throws \ActiveCollab\Narrative\Error\NoValidatorError
     */
    private function callValidator($validator, $validator_data, &$response, array &$passes, array &$failures) {
      $method_name = 'validate' . ucfirst(preg_replace_callback("/_[a-z]?/", function($matches) {
        return strtoupper(ltrim($matches[0], "_"));
      }, $validator));

      if(method_exists($this, $method_name)) {
        $this->$method_name($validator_data, $response, $passes, $failures);
      } else {
        throw new NoValidatorError($validator);
      }
    }

    /**
     * Validate if we got proper HTTP status code
     *
     * @param mixed $validator_data
     * @param Response|int $response
     * @param array $passes
     * @param array $failures
     */
    protected function validateHttpCode($validator_data, &$response, array &$passes, array &$failures) {
      if($response instanceof Response) {
        $code = $response->getHttpCode();
      } elseif(is_int($response)) {
        $code = $response;
      } else {
        $code = null;
      }

      if($code && $code === $validator_data) {
        $passes[] = "Got HTTP code {$code}";
      } else {
        $failures[] = 'Expected HTTP code ' . $validator_data . ', got ' . $this->verboseVariableValue($code);
      }
    }

    /**
     * Validate if we response is JSON response
     *
     * @param mixed $validator_data
     * @param Response|int $response
     * @param array $passes
     * @param array $failures
     */
    protected function validateIsJson($validator_data, &$response, array &$passes, array &$failures) {
      if($response instanceof Response && $response->isJson()) {
        $passes[] = 'Response is JSON';
      } else {
        $failures[] = 'Response is not JSON';
      }
    }

    /**
     * Validate if we response is JSON response
     *
     * @param mixed $validator_data
     * @param Response|int $response
     * @param array $passes
     * @param array $failures
     */
    protected function validateJsonCountElements($validator_data, &$response, array &$passes, array &$failures) {
      if($response instanceof Response && $response->isJson()) {
        $json = $response->getJson();

        if(is_array($json)) {
          $count = count($json);

          if($count == $validator_data) {
            $passes[] = "JSON has {$count} elements";
          } else {
            $failures[] = "Expected {$validator_data} element, got {$count}";
          }
        } else {
          $failures[] = 'Expected an array or object, got ' . gettype($json);
        }
      } else {
        $failures[] = 'Response is not JSON';
      }
    }

    /**
     * Validate if we response is JSON response
     *
     * @param mixed $validator_data
     * @param Response|int $response
     * @param array $passes
     * @param array $failures
     * @throws \ActiveCollab\Narrative\Error\ValidatorParamsError
     */
    protected function validateJsonPath($validator_data, &$response, array &$passes, array &$failures) {
      if($response instanceof Response && $response->isJson()) {
        if(is_array($validator_data)) {
          $json = $response->getJson(); // Fetch JSON only when we have an array of checkers

          foreach($validator_data as $params) {
            if(is_array($params) && count($params) <= 3) {
              $compare_operation = 'exists';
              $compare_data = null;

              switch(count($params)) {
                case 1:
                  $path = $params[0]; break;
                case 2:
                  list($path, $compare_operation) = $params; break;
                case 3:
                  list($path, $compare_operation, $compare_data) = $params; break;
                default:
                  throw new ValidatorParamsError('json_path', 'Individual JSONPath is an array with at least one (path) and at max three elements (path, compare operation and compare data)');
              }

              list($path, $fetch_first) = $this->processJsonPath($path);

              $store = new JsonStore();
              $fetch = $store->get($json, $path);

              if($fetch_first && is_array($fetch)) {
                $fetch = array_shift($fetch);
              }

              switch($compare_operation) {
                case 'exists':
                  if($fetch) {
                    $passes[] = "Value found at {$path}";
                  } else {
                    $failures[] = "No value at {$path}";
                  }

                  break;
                case 'is':
                  if($fetch === $compare_data) {
                    $passes[] = "Value at '{$path}' is " . $this->verboseVariableValue($compare_data);
                  } else {
                    $failures[] = "Value at '{$path}' is " . $this->verboseVariableValue($fetch) . ", we expected " . $this->verboseVariableValue($compare_data);
                  }

                  break;
                case 'is_not':
                  if($fetch !== $compare_data) {
                    $passes[] = "Value at {$path} is not " . $this->verboseVariableValue($compare_data);
                  } else {
                    $failures[] = "Value at '{$path}' is " . $this->verboseVariableValue($compare_data) . ". It is not what we expected";
                  }

                  break;
              }
            } else {
              throw new ValidatorParamsError('json_path', 'Individual JSONPath is an array with at least one (path) and at max three elements (path, compare operation and compare data)');
            }
          }
        } else {
          throw new ValidatorParamsError('json_path', 'JSONPath validator expects an array of checkers');
        }
      } else {
        $failures[] = 'Response is not JSON';
      }
    }

    /**
     * Process JSONPath and see if we need to featch first element or an entire array
     *
     * @param string $path
     * @return array
     */
    private function processJsonPath($path) {
      $path_length = strlen($path);

      if(substr($path, $path_length - 6) == '~first') {
        return [ substr($path, 0, $path_length - 6), true ];
      } else {
        return [ $path, false ];
      }
    }

    /**
     * Return var value in a nice to read format
     *
     * @param string $var
     * @return string
     */
    private function verboseVariableValue($var) {
      if(is_array($var)) {
        $result = '[';

        foreach($var as $k => $v) {
          $result .= ' "' . $k . '" => ' . $this->verboseVariableValue($v) . ',';
        }

        return rtrim($result, ',') . ' ]';
      } elseif(is_object($var)) {
        return 'object of class ' . get_class($var);
      } else {
        return '(' . gettype($var) . ') "' . (string) $var . '"';
      }
    } // verboseVariableValue

    // ---------------------------------------------------
    //  Fields and Bits
    // ---------------------------------------------------

    /**
     * Return request method
     *
     * @return string
     */
    function getMethod() {
      return $this->method;
    }

    /**
     * Return request path
     *
     * @return string
     */
    function getPath() {
      return $this->source['path'];
    }

    /**
     * Return request payload
     *
     * @return array|null
     */
    function getPayload() {
      return isset($this->source['payload']) ? $this->source['payload'] : null;
    }

    /**
     * Return array of files that need to be uploaded with this request
     *
     * @return array
     */
    function getAttachments() {
      return [];
    }

    /**
     * Return true if this is preparation request
     *
     * @return bool
     */
    function isPreparation() {
      return isset($this->source['prep']) && $this->source['prep'];
    }

    /**
     * Dump response
     *
     * @return bool
     */
    function dumpResponse() {
      return isset($this->source['dump_response']) && $this->source['dump_response'];
    }

    /**
     * Trigger when we are done loading content
     *
     * @return $this|Request
     * @throws \ActiveCollab\Narrative\Error\ParseJsonError
     */
    function &doneAddingLines() {
      $this->source .= "\n}";

      $decoded = json_decode($this->source, true);
      $json_error = json_last_error();

      if($decoded === null && $json_error !== JSON_ERROR_NONE) {
        throw new ParseJsonError($this->source, $json_error);
      } else {
        $this->source = $decoded;
      }

      return $this;
    }

  }