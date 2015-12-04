<?php

namespace ActiveCollab\Narrative\Command;

use ActiveCollab\Narrative\Project;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * @package ActiveCollab\Narrative\Command
 */
class StoriesCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('stories')
            ->setDescription('List stories in project');
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = new Project(getcwd());

        if ($project->isValid()) {
            $stories = $project->getStories();

            if (count($stories)) {
                $table = new Table($output);
                $table->setHeaders(['Name', 'Group']);

                foreach ($stories as $story) {
                    $table->addRow([$story->getName(), implode(' / ', $story->getGroups())]);
                }

                $table->render();
            }

            if (count($stories) === 1) {
                $output->writeln('1 story found');
            } else {
                $output->writeln(count($stories) . ' stories found');
            }
        } else {
            $output->writeln($project->getPath() . ' is not a valid Narrative project');
        }
    }
}
