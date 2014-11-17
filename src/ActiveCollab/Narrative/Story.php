<?php
  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative\StoryElement\Sleep;
  use ActiveCollab\Narrative\StoryElement\StoryElement;
  use ActiveCollab\Narrative\StoryElement\Text;
  use ActiveCollab\Narrative\StoryElement\Request;
  use ActiveCollab\Narrative\Error\ParseError;

  final class Story
  {
    /**
     * Story defintion path
     *
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $groups;

    /**
     * Construct the story instance
     *
     * @param string $story_path
     * @param string $stories_path
     */
    function __construct($story_path, $stories_path) {
      if (DIRECTORY_SEPARATOR === '\\') {
        $story_path = str_replace('\\', '/', $story_path);
        $stories_path = str_replace('\\', '/', $stories_path);
      }

      $this->path = $story_path;
      $this->name = $this->getNameFromPath();
      $this->groups = $this->getGroupsFromPath($stories_path);
    }

    /**
     * Get story name from story path
     *
     * @return string
     */
    private function getNameFromPath()
    {
      $basename = basename($this->path);

      if (substr_count($basename, '.') == 2) {
        $first_dot = strpos($basename, '.');
        $second_dot = strpos($basename, '.', $first_dot + 1);

        return trim(substr($basename, $first_dot + 1, $second_dot - $first_dot - 1));
      } else {
        return basename($this->path, '.narr');
      }
    }

    /**
     * Return groups from path
     *
     * @param string $stories_path
     * @return array
     */
    private function getGroupsFromPath($stories_path)
    {
      $groups = trim(substr(dirname($this->path), strlen($stories_path)), '/');

      return $groups ? explode('/', $groups) : [];
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
      return $this->name;
    }

    /**
     * Return story groups
     *
     * @return array
     */
    public function getGroups()
    {
      return $this->groups;
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
          } elseif($current_element instanceof Sleep) {
            throw new ParseError("Can't open a sleep command on line {$line_number} because sleep command is already open, but not closed");
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
          case 'SLEEP {':
            $current_element = new Sleep(); break;

          // Close a block
          case '}':
            if($current_element instanceof Sleep || $current_element instanceof Request) {
              $result[] = $current_element->doneAddingLines();
              $current_element = null;
            } else {
              throw new ParseError("Can't close a request or sleep command on line {$line_number}. There is no request open");
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