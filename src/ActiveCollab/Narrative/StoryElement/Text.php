<?php

  namespace ActiveCollab\Narrative\StoryElement;

  /**
   * Documentation text element
   *
   * @package ActiveCollab\Narrative\StoryElement
   */
  class Text extends StoryElement {

    /**
     * Trigger when we are done loading content
     *
     * @return $this|Text
     */
    function &doneAddingLines() {
      $this->source = trim($this->source);

      return $this;
    }

  }