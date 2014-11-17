<?php

  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative\Error\Error;
  use ActiveCollab\Narrative\StoryElement\Request, ActiveCollab\Narrative\StoryElement\Sleep;
  use ActiveCollab\Narrative\Error\CommandError, ActiveCollab\Narrative\Error\ParseJsonError, ActiveCollab\Narrative\Error\ParseError, ActiveCollab\Narrative\Error\ThemeNotFoundError;
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
     * Return configuration option
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfigurationOption($name, $default = null)
    {
      return isset($this->configuration[$name]) && $this->configuration[$name] ? $this->configuration[$name] : $default;
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
     * @var array
     */
    private $stories = false;

    /**
     * Return all project stories
     *
     * @return Story[]
     */
    public function getStories() {
      if ($this->stories === false) {
        $this->stories = [];

        $story_discoverer = $this->getStoryDiscoverer();

        $story_files = $story_discoverer->getStoryFiles("$this->path/stories");

        if (is_array($story_files)) {
          sort($story_files);

          foreach ($story_files as $story_file) {
            $this->stories[] = new Story($story_file, "$this->path/stories");
          }
        }
      }

      return $this->stories;
    }

    /**
     * @var StoryDiscoverer
     */
    private $story_discoverer;

    /**
     * Return story discoverer instance
     *
     * @return StoryDiscoverer
     */
    public function getStoryDiscoverer()
    {
      if (empty($this->story_discoverer)) {
        $this->story_discoverer = new StoryDiscoverer();
      }

      return $this->story_discoverer;
    }

    /**
     * Set custom story discoverer
     *
     * @param StoryDiscoverer $discoverer
     * @throws Error
     */
    public function setStoryDiscoverer($discoverer)
    {
      if ($discoverer instanceof StoryDiscoverer || $discoverer === null) {
        $this->story_discoverer = $discoverer;
        $this->stories = false; // Reset stories cache
      } else {
        throw new Error('Valid StoryDiscoverer instance or NULL expected');
      }
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

    /**
     * @var string
     */
    private $default_build_target;

    /**
     * Return default build target
     *
     * @return string|null
     */
    function getDefaultBuildTarget()
    {
      if (empty($this->default_build_target)) {
        $this->default_build_target = $this->getConfigurationOption('default_build_target');

        if (empty($this->default_build_target)) {
          $this->default_build_target = $this->path . '/build';
        }
      }

      return $this->default_build_target;
    }

    /**
     * Return build theme
     *
     * @param string|null $name
     * @return Theme
     * @throws ThemeNotFoundError
     */
    function getBuildTheme($name = null)
    {
      if ($name) {
        $theme_path = __DIR__ . "/Themes/$name"; // Input
      } elseif (is_dir($this->getPath() . '/theme')) {
        $theme_path = $this->getPath() . '/theme'; // Project specific theme
      } else {
        $theme_path = __DIR__ . "/Themes/" . $this->getDefaultBuildTheme(); // Default built in theme
      }

      if ($theme_path && is_dir($theme_path)) {
        return new Theme($theme_path);
      } else {
        throw new ThemeNotFoundError($name, $theme_path);
      }
    }

    /**
     * Return name of the default build theme
     *
     * @return string
     */
    function getDefaultBuildTheme()
    {
      return $this->getConfigurationOption('default_build_theme', 'bootstrap');
    }
  }