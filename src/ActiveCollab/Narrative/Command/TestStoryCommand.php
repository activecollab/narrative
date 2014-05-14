<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative\Error\ParseError;
  use ActiveCollab\Narrative\Error\ParseJsonError;
  use ActiveCollab\Narrative\Project;
  use ActiveCollab\Narrative\Story;
  use ActiveCollab\Narrative\StoryElement\Request;
  use ActiveCollab\SDK\Exception;
  use ActiveCollab\SDK\Response;
  use Symfony\Component\Console\Command\Command;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;

  /**
   * List stories command
   *
   * @package Narrative\Command
   */
  class TestStoryCommand extends Command {

    /**
     * Configure command
     */
    protected function configure()
    {
      $this->setName('test_story')->addArgument('story')->setDescription('Test if story requests return expected results');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $project = new Project(getcwd());

      if($project->isValid()) {
        $story_name = $input->getArgument('story');

        $story = $project->getStory($story_name);

        if($story instanceof Story) {
          try {
            $elements = $story->getElements();

            if($elements) {
              $output->writeln('Story "' . $story->getName() . '" loaded from file "' . $story->getPath() . '".');
              $project->setUp($output);

              try {
                $output->writeln('');

                $total_requests = $failed_requests = $total_assertions = $total_passes = $total_failures = 0;

                foreach($elements as $element) {
                  if($element instanceof Request) {
                    list($response, $passes, $failures) = $element->execute($output);

                    if($response instanceof Response && $element->dumpResponse()) {
                      print $response->getBody();
                    }

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
                  }
                }
              } catch(Exception $e) {
                $project->tearDown($output); // Make sure that we tear down the environment in case of an error
                throw $e;
              }

              $project->tearDown($output); // Make sure that we tear down the environment after each request

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
            }
          } catch(ParseError $e) {
            $output->writeln($e->getMessage());
          } catch(ParseJsonError $e) {
            $output->writeln($e->getMessage());
            $output->writeln($e->getJson());
          } catch(\Exception $e) {
            $output->writeln($e->getMessage());
          }
        } else {
          $output->writeln("Story '{$story_name}' not found");
        }
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }

    }

  }