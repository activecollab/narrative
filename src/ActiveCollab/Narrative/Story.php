<?php

  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative\StoryElement\StoryElement;
  use ActiveCollab\Narrative\StoryElement\Text;
  use ActiveCollab\Narrative\StoryElement\Request;
  use ActiveCollab\Narrative\Error\ParseError;

  final class Story {

    /**
     * Story defintion path
     *
     * @var string
     */
    private $path;

    /**
     * Construct the story instance
     *
     * @param $path
     */
    function __construct($path) {
      $this->path = $path;
    }

    /**
     * Return path to the story definition file
     *
     * @return string
     */
    function getPath() {
      return $this->path;
    }

    /**
     * Return a story name
     *
     * @return string
     */
    function getName() {
      return basename($this->path, '.narr');
    }

    /**
     * Parse the story and return a list of story elements
     *
     * @return StoryElement[]
     * @throws Error\ParseError
     */
    function getElements() {
      $result = [];

      $current_element = $line_number = null;

      foreach($lines = file($this->path) as $line) {
        $line_number++;

        $line = rtrim($line, "\n");

        // Close current element
        if($current_element && ($line === 'GET {' || $line === 'POST {' || $line === 'PUT {' || $line === 'DELETE {')) {

          // Close text
          if($current_element instanceof Text) {
            $result[] = $current_element->doneAddingLines();
            $current_element = null;
          } elseif($current_element instanceof Request) {
            throw new ParseError("Can't open a request on line {$line_number} because request is already open, but not closed");
          } // if
        }

        switch($line) {
          case 'GET {':
            $current_element = new Request(Request::GET); break;
          case 'POST {':
            $current_element = new Request(Request::POST); break;
          case 'PUT {':
            $current_element = new Request(Request::PUT); break;
          case 'DELETE {':
            $current_element = new Request(Request::DELETE); break;

          // Close a request
          case '}':
            if($current_element instanceof Request) {
              $result[] = $current_element->doneAddingLines();
              $current_element = null;
            } else {
              throw new ParseError("Can't close a request on line {$line_number}. There is no request open");
            }

            break;

          // Empty line. Skip unless in the middle of the element (in which case it's a regular element content line).
          case '':
            if($current_element instanceof StoryElement) {
              $current_element->addLine($line);
            }

            break;

          // Add a line to the current element, or create a new text element
          default:
            if(empty($current_element)) {
              $current_element = new Text();
            }

            $current_element->addLine($line);
        }
      }

      if($current_element instanceof Text) {
        $result[] = $current_element->doneAddingLines();
      } elseif($current_element instanceof Request) {
        throw new ParseError("Open request not closed at line {$line_number}");
      }

      return $result;
    }

  }