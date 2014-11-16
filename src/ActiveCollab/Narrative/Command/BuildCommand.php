<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative\Error\ParseError;
  use ActiveCollab\Narrative\Error\ParseJsonError;
  use ActiveCollab\Narrative\Project;
  use ActiveCollab\Narrative\Story;
  use ActiveCollab\Narrative\StoryElement\Request;
  use ActiveCollab\Narrative\TestResult;
  use ActiveCollab\SDK\Exception;
  use ActiveCollab\SDK\Response;
  use Symfony\Component\Console\Command\Command;
  use Symfony\Component\Console\Input\InputInterface;
  use Symfony\Component\Console\Input\InputOption;
  use Symfony\Component\Console\Output\OutputInterface;

  /**
   * Build documentation command
   *
   * @package Narrative\Command
   */
  class BuildCommand extends Command
  {
    /**
     * Configure command
     */
    protected function configure()
    {
      $this->setName('build')->setDescription('Build documentation');
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
        $project->getStories();
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }
    }
  }