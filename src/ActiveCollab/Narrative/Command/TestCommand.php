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
   * Test all stories command
   *
   * @package Narrative\Command
   */
  class TestCommand extends Command {

    /**
     * Configure command
     */
    protected function configure()
    {
      $this->setName('test')->setDescription('Test all requests from all stories');
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
        $stories = $project->getStories();

        if(count($stories)) {
          $project->testStories($stories, $output);
        } else {
          $output->writeln('There are no stories in this project');
        }
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }

    }

  }