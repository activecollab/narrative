<?php

  namespace ActiveCollab\Narrative\StoryElement;

  use ActiveCollab\Narrative\Project;
  use ActiveCollab\Narrative\Error\ParseJsonError;
  use Symfony\Component\Console\Output\OutputInterface;

  /**
   * Documentation text element
   *
   * @package ActiveCollab\Narrative\StoryElement
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
     * @param OutputInterface $output
     * @return array
     */
    function execute(Project $project, $output) {
      $how_long = $this->howLong();

      $output->writeln("<info>Zzzzzzzzzz...</info> <question>[{$how_long}s]</question>");
      sleep($how_long);
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
    private function howLong() {
      return isset($this->source['seconds']) && $this->source['seconds'] > 0 ? (integer) $this->source['seconds'] : 1;
    }

  }