<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use ActiveCollab\Narrative\Command;
use Symfony\Component\Console\Application;

$application = new Application('Narrative', trim(file_get_contents(dirname(__DIR__) . '/VERSION')));

foreach (new DirectoryIterator(dirname(__DIR__) . '/src/Command') as $file) {
    if ($file->isFile()) {
        $class_name = ('\\ActiveCollab\\Narrative\\Command\\' . $file->getBasename('.php'));

        if ((new ReflectionClass($class_name))->isAbstract()) {
            continue;
        }

        $application->add(new $class_name);
    }
}

$application->run();
