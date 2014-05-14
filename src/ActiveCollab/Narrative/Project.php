<?php

  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative\Error\CommandError;
  use ActiveCollab\Narrative\Error\ParseJsonError;
  use Symfony\Component\Console\Output\OutputInterface;

  /**
   * Narrative project
   *
   * @package Narrative
   */
  final class Project {

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
    function __construct($path) {
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
    function getName() {
      return isset($this->configuration['name']) && $this->configuration['name'] ? $this->configuration['name'] : basename($this->path);
    }

    /**
     * Return project path
     *
     * @return string
     */
    function getPath() {
      return $this->path;
    }

    /**
     * Return all project stories
     *
     * @return Story[]
     */
    function getStories() {
      $result = [];

      if(is_dir("$this->path/stories")) {

        /**
         * @var \DirectoryIterator[] $dir
         */
        $dir = new \DirectoryIterator("$this->path/stories");

        foreach($dir as $file) {
          if($file->isDot() || $file->isDir() || $file->getExtension() != 'narr') {
            continue;
          } // if

          $result[] = new Story($file->getPathname());
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
    function getStory($name) {
      foreach($this->getStories() as $story) {
        if($story->getName() === $name) {
          return $story;
        }
      }

      return null;
    }

    /**
     * Return true if this is a valid project
     *
     * @return bool
     */
    function isValid() {
      return is_dir($this->path) && is_file($this->path . '/project.json');
    }

    // ---------------------------------------------------
    //  Set up and tear down
    // ---------------------------------------------------

    /**
     * Set up the environment before each story
     *
     * @param OutputInterface $output
     */
    function setUp(OutputInterface $output) {
      if(isset($this->configuration['set_up']) && is_array($this->configuration['set_up'])) {
        foreach($this->configuration['set_up'] as $command) {
          $this->executeCommand($command);
        }
      }
    }

    /**
     * Tear down before each story
     *
     * @param OutputInterface $output
     */
    function tearDown(OutputInterface $output) {
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

  }