<?php

namespace ActiveCollab\Narrative\Command;

use ActiveCollab\Narrative\Project;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package ActiveCollab\Narrative\Command
 */
class RoutesCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('routes')
            ->setDescription('List routes in project');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = new Project(getcwd());

        if ($project->isValid()) {
            $routes = $project->getRoutes();

            if ($routes) {
                $table = new Table($output);
                $table->setHeaders(['Route', 'Methods']);
                foreach ($routes as $route => $route_details) {
                    $table->addRow([$route, (isset($route_details['methods']) && count($route_details['methods']) ? implode(', ', $route_details['methods']) : '')]);
                }
                $table->render();
            }

            if (count($routes) === 1) {
                $output->writeln('1 route found');
            } else {
                $output->writeln(count($routes) . ' route found');
            }
        } else {
            $output->writeln($project->getPath() . ' is not a valid Narrative project');
        }
    }
}
