<?php

namespace ActiveCollab\Narrative\Command;

use ActiveCollab\Narrative\Error\ParseError;
use ActiveCollab\Narrative\Error\ParseJsonError;
use ActiveCollab\Narrative\Project;
use ActiveCollab\Narrative\Story;
use ActiveCollab\Narrative\StoryElement\Text;
use ActiveCollab\Narrative\StoryElement\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package ActiveCollab\Narrative\Command
 */
class StoryCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('story')
            ->addArgument('story')
            ->setDescription('Show story details');
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
            $story_name = $input->getArgument('story');

            $story = $project->getStory($story_name);

            if ($story instanceof Story) {
                try {
                    $elements = $story->getElements();

                    if ($elements) {
                        $output->writeln('Story "' . $story->getName() . '" loaded from file "' . $story->getPath() . '".');
                        $output->writeln('');
                        $output->writeln('Elements:');
                        $output->writeln('');

                        foreach ($elements as $element) {
                            if ($element instanceof Text) {
                                $output->writeln('- Block of text: ' . substr($element->getSource(), 0, 45) . '...');
                            } elseif ($element instanceof Request) {
                                $output->writeln('- ' . $element->getMethod() . ' ' . $element->getPath() . ($element->isPreparation() ? ' [PREP]' : ''));
                            }
                        }

                        $output->writeln('');
                    }
                } catch (ParseError $e) {
                    $output->writeln($e->getMessage());
                } catch (ParseJsonError $e) {
                    $output->writeln($e->getMessage());
                    $output->writeln($e->getJson());
                } catch (\Exception $e) {
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
