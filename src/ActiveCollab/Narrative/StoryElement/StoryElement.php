<?php

  namespace ActiveCollab\Narrative\StoryElement;

  abstract class StoryElement {

    /**
     * Element source code
     *
     * @var string|array
     */
    protected $source;

    /**
     * Return loaded source
     *
     * @return string|array
     */
    function getSource() {
      return $this->source;
    }

    /**
     * Add line of text to the source
     *
     * @param $line
     */
    function addLine($line) {
      if($this->source) {
        $this->source .= "\n";
      }

      $this->source .= $line;
    } // addLine

    /**
     * Trigger when we are done loading content
     *
     * @return StoryElement
     */
    abstract function &doneAddingLines();

  }