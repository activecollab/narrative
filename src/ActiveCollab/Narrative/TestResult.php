<?php
  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative\Error\ParseError;
  use ActiveCollab\Narrative\Error\ParseJsonError;
  use Symfony\Component\Console\Output\OutputInterface, Symfony\Component\Console\Helper\Table, \Exception;
  use ActiveCollab\Narrative\StoryElement\Request, ActiveCollab\Narrative\Connector\Connector;
  use ActiveCollab\SDK\Response;
  use League\Csv\Reader;

  /**
   * Collect information about tests as they are executed
   *
   * @package ActiveCollab\Narrative
   */
  final class TestResult
  {
    /**
     * @var int
     */
    private $total_stories = 0;

    /**
     * @var int
     */
    private $total_requests = 0, $failed_requests = 0;

    /**
     * @var int
     */
    private $total_assertions = 0, $total_passes = 0, $total_failures = 0;

    /**
     * @var int
     */
    private $total_request_time = 0, $total_sleep_time = 0;

    /**
     * @var float
     */
    private $construct_time;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $output_filter = [
      'set_up_tear_down' => true,
      'request_failure_exception' => true,
      'presona_created' => true,
      'request_details' => true,
      'sleep_time' => true,
    ];

    /**
     * Construct new test result instance
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface &$output)
    {
      $this->output = $output;
      $this->construct_time = microtime(true);
    }

    /**
     * @var Story|null
     */
    private $current_story;

    /**
     * Record story set-up
     *
     * @param Story $story
     */
    public function storySetUp(Story $story)
    {
      $this->current_story = $story;

      if ($this->output_filter['set_up_tear_down']) {
        $this->output->writeln('Setting up the test environment for "' . $story->getName() . '" story');
      }
    }

    /**
     * Record story tear-down
     */
    public function storyTearDown()
    {
      $this->total_stories++;

      if ($this->output_filter['set_up_tear_down']) {
        $this->output->writeln('Tearing down the test environment');
      }

      $this->current_story = null;
    }

    /**
     * @var Request|null
     */
    private $current_request;

    /**
     * @param Request $request
     */
    public function requestSetUp(Request $request)
    {
      $this->current_request = $request;
    }

    /**
     * @param Response|int $response
     * @param array $passes
     * @param array $failures
     * @param float|null $request_time
     * @param string $persona
     * @param boolean $is_prep
     * @param boolean $dump_response
     */
    public function requestTearDown($response, $passes, $failures, $request_time, $persona, $is_prep, $dump_response)
    {
      $this->total_requests++;

      if (is_array($passes)) {
        $this->total_assertions += count($passes);
        $this->total_passes += count($passes);
      }

      if (is_array($failures)) {
        $this->total_assertions += count($failures);
        $this->total_failures += count($failures);
      }

      $request_time = $response instanceof Response ? $response->getTotalTime() : (float) $request_time;

      $this->total_request_time += $request_time;

      if ($this->output_filter['request_details']) {
        $this->writeRequestMessage($response, $failures, $request_time, $persona, $is_prep, $dump_response);
      }

      $this->current_request = null;
    }

    /**
     * @param Response|int $response
     * @param array $failures
     * @param float|null $request_time
     * @param string $persona
     * @param boolean $is_prep
     * @param boolean $dump_response
     */
    private function writeRequestMessage($response, $failures, $request_time, $persona, $is_prep, $dump_response)
    {
      if(empty($failures)) {
        $color = 'info';
      } else {
        $color = 'error';
        $this->output->writeln('');
      }

      $http_code = $response instanceof Response ? $response->getHttpCode() : (integer) $response;
      $prep = $is_prep ? ' <question>[PREP]</question>' : '';
      $as = $persona != Connector::DEFAULT_PERSONA ? ' <question>[AS ' . $persona .']</question>' : '';

      $this->output->writeln("<$color>" . $this->current_request->getMethod() . ' ' . $this->current_request->getPathWithQueryString() . " - {$http_code} in {$request_time} seconds</$color>{$prep}{$as}");

      // Output failure lines
      if(count($failures)) {
        foreach($failures as $failure) {
          $this->output->writeln('<error>- ' . $failure . '</error>');
        }
        $this->output->writeln('');
      }

      // Output response, if needed
      if($dump_response && $response instanceof Response) {

        // JSON
        if($response->isJson()) {
          ob_start();
          print_r($response->getJson());
          $this->output->write(ob_get_clean());

          // CSV
        } elseif ($response->getContentType() == 'text/csv') {
          $table = new Table($this->output);

          $header_rendered = false;

          foreach (Reader::createFromString(trim($response->getBody()))->fetchAll() as $row) {
            if ($header_rendered) {
              $table->addRow($row);
            } else {
              $table->setHeaders($row);
              $header_rendered = true;
            }
          }

          $table->render();

        // Plain text
        } else {
          $this->output->writeln($response->getBody());
        }
      }
    }

    /**
     * Record request failure
     *
     * @param Exception|null $exception
     */
    public function requestFailure($exception = null)
    {
      if ($exception instanceof Exception && $this->output_filter['request_failure_exception']) {
        $this->output->writeln('<error>Failed to execute ' . $this->current_request->getPath() . '. Reason: ' . $exception->getMessage() . '</error>');

        if(method_exists($exception, 'getServerResponse')) {
          print_r($exception->getServerResponse());
        }
      }

      $this->total_requests++;
      $this->failed_requests++; // @TODO Record story and request details, not just the count?

      $this->current_request = null;
    }

    /**
     * Record that persona has been created
     *
     * @param string $name
     * @param string $token
     */
    public function personaCreated($name, $token)
    {
      if ($this->output_filter['presona_created']) {
        $this->output->writeln("<comment>INFO Persona '{$name}' has been created (access token '{$token}')</comment>");
      }
    }

    /**
     * Record execution sleep time
     *
     * @param integer $how_long
     */
    public function executionSleep($how_long)
    {
      if ($this->output_filter['sleep_time']) {
        $this->output->writeln("<info>Zzzzzzzzzz...</info> <question>[{$how_long}s]</question>");
      }

      $this->total_sleep_time += $how_long;
    }

    public function parseError(ParseError $e)
    {
      $this->output->writeln($e->getMessage());
    }

    public function parseJsonError(ParseJsonError $e)
    {
      $this->output->writeln($e->getMessage());
      $this->output->writeln($e->getJson());
    }

    public function requestExecutionError(Exception $e)
    {
      $this->output->writeln($e->getMessage());
    }

    /**
     * Print test conclusion
     */
    public function conclude()
    {
      $this->output->writeln('');

      $this->output->writeln('Stories: ' . $this->total_stories . '.');

      if ($this->failed_requests) {
        $stats = "Requests: {$this->total_requests}. <error>Failures: {$this->failed_requests}</error>. ";
      } else {
        $stats = "Requests: {$this->total_requests}. Failures: {$this->failed_requests}. ";
      }

      if ($this->total_failures) {
        $stats .= "Assertions: {$this->total_assertions}. Passes: {$this->total_passes}. <error>Failures: {$this->total_failures}</error>.";
      } else {
        $stats .= "Assertions: {$this->total_assertions}. Passes: {$this->total_passes}. Failures: {$this->total_failures}.";
      }

      $this->output->writeln($stats);

      $time_stats = 'Execution Time: ' . $this->getExecutionTime() . 's. Request Time: ' . $this->getTotalRequestTime() . 's. Sleep Time: ' . $this->total_sleep_time . 's.';

      $this->output->writeln($time_stats);

      $this->output->writeln('');
    }

    /**
     * @return float
     */
    private function getExecutionTime()
    {
      return round(microtime(true) - $this->construct_time, 2);
    }

    /**
     * @return float
     */
    private function getTotalRequestTime()
    {
      return round($this->total_request_time, 2);
    }
  }