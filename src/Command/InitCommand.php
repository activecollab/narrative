<?php

namespace ActiveCollab\Narrative\Command;

use ActiveCollab\Narrative\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Init Narrative project command
 *
 * @package ActiveCollab\Narrative\Narrative\Command
 */
class InitCommand extends Command
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('init')->setDescription('Initialize a new project');
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = new Project(getcwd());

        if ($project->isValid()) {
            $output->writeln('Project already initialized');
        } else {
            // Write project.json
            // Create /build folder
            // Create /files folder
            // Create /stories folder
            // Create /temp folder
        }
    }
}
