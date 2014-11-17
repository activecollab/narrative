<?php

  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative\StoryElement\Request, ActiveCollab\Narrative\StoryElement\Sleep;
  use ActiveCollab\Narrative\Error\CommandError, ActiveCollab\Narrative\Error\ParseJsonError, ActiveCollab\Narrative\Error\ParseError;
  use ActiveCollab\Narrative\Connector\Connector, ActiveCollab\Narrative\Connector\ActiveCollabSdkConnector, ActiveCollab\SDK\Exception;

  /**
   * Narrative project
   *
   * @package Narrative
   */
  final class Project
  {
    /**
     * @var string
     */
    private $path;

    /**
     * Configuration data
     *
     * @var array
     */
    private $configuration = [];

    /**
     * Create a new project instance
     *
     * @param string $path
     * @throws |ActiveCollab\Narrative\Error\ParseJsonError
     */
    public function __construct($path) {
      $this->path = $path;

      if($this->isValid()) {
        $configuration_json = file_get_contents($this->path . '/project.json');
        $this->configuration = json_decode($configuration_json, true);

        if($this->configuration === null) {
          throw new ParseJsonError($configuration_json, json_last_error());
        }

        if(empty($this->configuration)) {
          $this->configuration = [];
        }
      }
    }

    /**
     * Return project name
     *
     * @return string
     */
    public function getName() {
      return isset($this->configuration['name']) && $this->configuration['name'] ? $this->configuration['name'] : basename($this->path);
    }

    /**
     * Return project path
     *
     * @return string
     */
    public function getPath() {
      return $this->path;
    }

    /**
     * Connector instance
     *
     * @var Connector
     */
    private $connector = false;

    /**
     * @return Connector
     */
    public function &getConnector() {
      if($this->connector === false) {
        $this->connector = new ActiveCollabSdkConnector([ 'url' => 'http://feather.dev', 'token' => '1-TESTTESTTESTTESTTESTTESTTESTTESTTESTTEST' ]);
      }

      return $this->connector;
    }

    /**
     * Return all project stories
     *
     * @return Story[]
     */
    public function getStories() {
      $result = [];

      if(is_dir("$this->path/stories")) {
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("$this->path/stories")) as $file) {

          /**
           * @var \DirectoryIterator $file
           */
          if(substr($file->getBasename(), 0, 1) != '.' && $file->getExtension() == 'narr') {
            $result[] = new Story($file->getPathname());
          }
        }
      }

      return $result;
    }

    /**
     * Return story by name
     *
     * @param string $name
     * @return Story|null
     */
    public function getStory($name) {
      foreach($this->getStories() as $story) {
        if($story->getName() === $name) {
          return $story;
        }
      }

      return null;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
      return isset($this->configuration['routes']) && is_array($this->configuration['routes']) ? $this->configuration['routes'] : [];
    }

    /**
     * Return route names from path
     *
     * @param string $path
     * @return string
     */
    public function getRouteNameFromPath($path)
    {
      if (isset($this->configuration['routes']) && is_array($this->configuration['routes'])) {
        $path = trim($path, '/');

        foreach (array_reverse($this->configuration['routes']) as $route_name => $route_settings) {
          if (preg_match($route_settings['match'], $path)) {
            return $route_name;
          }
        }
      }

      return '';
    }

    /**
     * Return true if this is a valid project
     *
     * @return bool
     */
    public function isValid() {
      return is_dir($this->path) && is_file($this->path . '/project.json');
    }

    /**
     * Test a group of stories
     *
     * @param Story[] $stories
     * @param TestResult $test_result
     */
    public function testStories(array $stories, TestResult &$test_result)
    {
      foreach ($stories as $story) {
        try {
          if ($elements = $story->getElements()) {
            $this->setUp($story, $test_result);

            try {
              $variables = [];

              foreach ($elements as $element) {
                if ($element instanceof Request) {
                  $element->execute($this, $variables, $test_result);
                } elseif ($element instanceof Sleep) {
                  $element->execute($this, $test_result);
                }
              }
            } catch (Exception $e) {
              $this->tearDown($test_result); // Make sure that we tear down the environment in case of an error
              throw $e;
            }

            $this->tearDown($test_result); // Make sure that we tear down the environment after each request
          }
        } catch (ParseError $e) {
          $test_result->parseError($e);
        } catch (ParseJsonError $e) {
          $test_result->parseJsonError($e);
        } catch (\Exception $e) {
          $test_result->requestExecutionError($e);
        }
      }
    }

    // ---------------------------------------------------
    //  Set up and tear down
    // ---------------------------------------------------

    /**
     * Set up the environment before each story
     *
     * @param Story $story
     * @param TestResult $test_result
     */
    public function setUp(Story $story, TestResult &$test_result) {
      $test_result->storySetUp($story);

      if(isset($this->configuration['set_up']) && is_array($this->configuration['set_up'])) {
        foreach($this->configuration['set_up'] as $command) {
          $this->executeCommand($command);
        }
      }
    }

    /**
     * Tear down before each story
     *
     * @param TestResult $test_result
     */
    public function tearDown(TestResult &$test_result) {
      $test_result->storyTearDown();

      if(isset($this->configuration['tear_down']) && is_array($this->configuration['tear_down'])) {
        foreach($this->configuration['tear_down'] as $command) {
          $this->executeCommand($command);
        }
      }
    }

    /**
     * Execute set up or tear down command
     *
     * @param mixed $c
     * @return string
     * @throws Error\CommandError
     */
    private function executeCommand($c) {
      $original_working_dir = getcwd();

      if(is_string($c)) {
        $command = $c;
      } elseif(is_array($c) && count($c)) {
        list($command, $working_dir) = $c;

        if($working_dir != $original_working_dir) {
          chdir($working_dir);
        }
      } else {
        throw new CommandError($c);
      }

      $result = exec($command);

      if(getcwd() != $original_working_dir) {
        chdir($original_working_dir);
      }

      return $result;
    }

    /**
     * Return temp path
     *
     * @return string
     */
    public function getTempPath()
    {
      return $this->path . '/temp';
    }
  }