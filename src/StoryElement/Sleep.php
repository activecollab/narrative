<?php
  namespace ActiveCollab\Narrative\StoryElement;

  use ActiveCollab\Narrative\Project;
  use ActiveCollab\Narrative\Error\ParseJsonError;
  use ActiveCollab\Narrative\TestResult;
  use Symfony\Component\Console\Output\OutputInterface;

  /**
   * Documentation text element
   *
   * @package ActiveCollab\Narrative\Narrative\StoryElement
   */
  class Sleep extends StoryElement {

    /**
     * Element source code
     *
     * @var string|array
     */
    protected $source = "{";

    /**
     * Execute the command
     *
     * @param Project $project
     * @param TestResult $test_result
     * @return array
     */
    function execute(Project $project, TestResult &$test_result) {
      $test_result->executionSleep($this->howLong());
      sleep($this->howLong());
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

    /**
     * How long should we sleep
     *
     * @return int
     */
    public function howLong() {
      return isset($this->source['seconds']) && $this->source['seconds'] > 0 ? (integer) $this->source['seconds'] : 1;
    }
  }