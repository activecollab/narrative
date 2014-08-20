<?php

  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative\StoryElement\Request;
  use ActiveCollab\Narrative\StoryElement\Sleep;
  use ActiveCollab\SDK\Exception;
  use ActiveCollab\Narrative\Error\CommandError;
  use ActiveCollab\Narrative\Error\ParseError;
  use ActiveCollab\Narrative\Error\ParseJsonError;
  use Symfony\Component\Console\Output\OutputInterface;
  use ActiveCollab\Narrative\Connector\Connector;
  use ActiveCollab\Narrative\Connector\ActiveCollabSdkConnector;

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
     * Connector instance
     *
     * @var Connector
     */
    private $connector = false;

    /**
     * @return Connector
     */
    function &getConnector() {
      if($this->connector === false) {
        $this->connector = new ActiveCollabSdkConnector([ 'url' => 'http://activecollab.back', 'token' => '1-TESTTESTTESTTESTTESTTESTTESTTESTTESTTEST' ]);
      }

      return $this->connector;
    }

    /**
     * Return all project stories
     *
     * @return Story[]
     */
    function getStories() {
      $result = [];

      if(is_dir("$this->path/stories")) {
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("$this->path/stories")) as $file) {

          /**
           * @var \DirectoryIterator $file
           */
          if(substr($file->getBasename(), 0, 1) != '.' && $file->getExtension() == 'narr') {
            $result[] = new Story($file->getPathname());
          } // if
        } // foreach
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

    /**
     * Test a group of stories
     *
     * @param Story[] $stories
     * @param OutputInterface $output
     */
    function testStories(array $stories, OutputInterface &$output) {
      $total_requests = $failed_requests = $total_assertions = $total_passes = $total_failures = 0;

      foreach($stories as $story) {
        try {
          if($elements = $story->getElements()) {
            $this->setUp($output, $story);

            try {
              $output->writeln('');

              $variables = [];

              foreach($elements as $element) {
                if($element instanceof Request) {
                  list($response, $passes, $failures) = $element->execute($this, $variables, $output);

                  $total_requests++;

                  if(empty($response) || is_array($failures) && count($failures)) {
                    $failed_requests ++;
                  }

                  if(is_array($passes)) {
                    $total_assertions += count($passes);
                    $total_passes += count($passes);
                  }

                  if(is_array($failures)) {
                    $total_assertions += count($failures);
                    $total_failures += count($failures);
                  }
                } elseif($element instanceof Sleep) {
                  $element->execute($this, $output);
                }
              }
            } catch(Exception $e) {
              $this->tearDown($output); // Make sure that we tear down the environment in case of an error
              throw $e;
            }

            $this->tearDown($output); // Make sure that we tear down the environment after each request
          }
        } catch(ParseError $e) {
          $output->writeln($e->getMessage());
        } catch(ParseJsonError $e) {
          $output->writeln($e->getMessage());
          $output->writeln($e->getJson());
        } catch(\Exception $e) {
          $output->writeln($e->getMessage());
        }
      }

      $output->writeln('');

      if($failed_requests) {
        $stats = "Requests: {$total_requests}. <error>Failures: {$failed_requests}</error>. ";
      } else {
        $stats = "Requests: {$total_requests}. Failures: {$failed_requests}. ";
      }

      if($total_failures) {
        $stats .= "Assertions: {$total_assertions}. Passes: {$total_passes}. <error>Failures: {$total_failures}</error>.";
      } else {
        $stats .= "Assertions: {$total_assertions}. Passes: {$total_passes}. Failures: {$total_failures}.";
      }

      $output->writeln($stats);
    } // testStories

    // ---------------------------------------------------
    //  Set up and tear down
    // ---------------------------------------------------

    /**
     * Set up the environment before each story
     *
     * @param OutputInterface $output
     * @param Story|null
     */
    function setUp(OutputInterface $output, $story = null) {
      if($story instanceof Story) {
        $output->writeln('Setting up the test environment for "' . $story->getName() . '" story');
      } else {
        $output->writeln('Setting up the test environment');
      }

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
      $output->writeln('Tearing down the test environment');

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