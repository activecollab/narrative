<?php

namespace ActiveCollab\Narrative\Command;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package ActiveCollab\Narrative\Command
 */
class UpdateCommand extends Command
{
    const MANIFEST_FILE = 'https://labs.activecollab.com/narrative/manifest.json';

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('update')->setDescription('Updates narrative.phar to the latest version');
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        $manager->update($this->getApplication()->getVersion(), true);
    }
}
