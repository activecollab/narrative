<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative\Project;
  use ActiveCollab\Narrative\Story;
  use Symfony\Component\Console\Command\Command, Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Output\OutputInterface;

  /**
   * This command will test all requests from a story
   *
   * @package Narrative\Command
   */
  class TestStoryCommand extends Command
  {
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
          $project->testStories([ $story ], $output);
        } else {
          $output->writeln("Story '{$story_name}' not found");
        }
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }

    }
  }