<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative\Project;
  use Symfony\Component\Console\Command\Command, Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Output\OutputInterface, Symfony\Component\Console\Helper\Table;

  /**
   * List stories command
   *
   * @package Narrative\Command
   */
  class StoriesCommand extends Command
  {
    /**
     * Configure command
     */
    protected function configure()
    {
      $this->setName('stories')->setDescription('List stories in project');
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

        if (count($stories)) {
          $table = new Table($output);
          $table->setHeaders([ 'Group', 'Name' ]);

          foreach($stories as $story) {
            $table->addRow([ implode(' / ', $story->getGroups()), $story->getName() ]);
          }

          $table->render();
        }

        if(count($stories) === 1) {
          $output->writeln('1 story found');
        } else {
          $output->writeln(count($stories) . ' stories found');
        }
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }
    }
  }