<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative;
  use ActiveCollab\Narrative\Project, ActiveCollab\Narrative\Theme, ActiveCollab\Narrative\Error\ThemeNotFoundError;
  use Symfony\Component\Console\Command\Command, Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Output\OutputInterface;
  use Smarty;

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
     * @var Smarty
     */
    private $smarty;

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

        $this->smarty =& Narrative::initSmarty($project, $theme);

        $this->prepareTargetPath($input, $output, $project, $target_path, $theme);

        $project->getStories();
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Project $project
     * @param $target_path
     * @param Theme $theme
     * @return bool
     */
    public function prepareTargetPath(InputInterface $input, OutputInterface $output, Project $project, $target_path, Theme $theme)
    {
      Narrative::clearDir($target_path, function($path) use (&$output) {
        $output->writeln("$path deleted");
      });

      Narrative::copyDir($theme->getPath() . '/assets', "$target_path/assets", function($path) use (&$output) {
        $output->writeln("$path copied");
      });

      return true;
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
     * Return true if target path is valid
     *
     * @param string $target_path
     * @return bool
     */
    private function isValidTargetPath($target_path)
    {
      return $target_path && is_dir($target_path);
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