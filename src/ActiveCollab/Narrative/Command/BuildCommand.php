<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative\Project, ActiveCollab\Narrative\Theme, ActiveCollab\Narrative\Error\ThemeNotFoundError;
  use Symfony\Component\Console\Command\Command, Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Output\OutputInterface;

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
      ini_set('date.timezone', 'UTC');

      $project = new Project(getcwd());

      if($project->isValid()) {
        $target_path = $this->getBuildTarget($input, $project);
        $theme = $this->getTheme($input, $project);

        if (!$this->isValidTargetPath($target_path)) {
          $output->writeln("Build target '$target_path' not found or not writable");
          return;
        }

        if (!($theme instanceof Theme)) {
          $output->writeln("Theme not found");
          return;
        }

        $project->getStories();
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }
    }

    /**
     * Return build target path
     *
     * @param InputInterface $input
     * @param Project $project
     * @return string
     */
    private function getBuildTarget(InputInterface $input, Project &$project)
    {
      $target = $input->getOption('target');

      if (empty($target)) {
        $target = $project->getDefaultBuildTarget();
      }

      return (string) $target;
    }

    /**
     * @param InputInterface $input
     * @param Project $project
     * @return Theme
     * @throws ThemeNotFoundError
     */
    private function getTheme(InputInterface $input, Project &$project)
    {
      return $project->getBuildTheme($input->getOption('theme'));
    }
  }