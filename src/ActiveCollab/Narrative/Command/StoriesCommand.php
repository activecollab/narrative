<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative\Project;
  use Symfony\Component\Console\Command\Command;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Output\OutputInterface;

  /**
   * List stories command
   *
   * @package Narrative\Command
   */
  class StoriesCommand extends Command {

    /**
     * Configure command
     */
    protected function configure()
    {
      $this->setName('stories')->setDescription('List all stories form this project');
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

        if(count($stories) === 1) {
          $output->writeln('1 story found');
        } else {
          $output->writeln(count($stories) . ' stories found');
        } // if

        foreach($stories as $story) {
          $output->writeln($story->getName());
        }
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }

    }

  }