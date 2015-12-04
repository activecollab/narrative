<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative\Project, ActiveCollab\Narrative\Story, ActiveCollab\Narrative\TestResult;
  use Symfony\Component\Console\Command\Command, Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Input\InputOption, Symfony\Component\Console\Output\OutputInterface;

  /**
   * Test all stories command
   *
   * @package Narrative\Command
   */
  class TestCommand extends Command
  {
    /**
     * Configure command
     */
    protected function configure()
    {
      $this
        ->setName('test')
        ->addArgument('story')
        ->addOption('coverage', null, InputOption::VALUE_NONE, 'Track route coverage')
        ->setDescription('Test stories');
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

        if (empty($story_name)) {
          $stories = $project->getStories();
        } else {
          $story = $project->getStory($story_name);

          if ($story instanceof Story) {
            $stories = [ $story ];
          } else {
            $output->writeln("Story '{$story_name}' not found");
            return;
          }
        }

        if(count($stories)) {
          $test_result = new TestResult($project, $output);
          $test_result->setTrackCoverage($input->getOption('coverage'));

          foreach ($stories as $story) {
            $project->testStory($story, $test_result);
          }

          $test_result->conclude();
        } else {
          $output->writeln('There are no stories in this project');
        }
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }
    }
  }