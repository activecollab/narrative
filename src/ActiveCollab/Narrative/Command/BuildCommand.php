<?php

  namespace ActiveCollab\Narrative\Command;

  use ActiveCollab\Narrative;
  use ActiveCollab\Narrative\Project, ActiveCollab\Narrative\Story, ActiveCollab\Narrative\Theme, ActiveCollab\Narrative\Error\ThemeNotFoundError;
  use Symfony\Component\Console\Command\Command, Symfony\Component\Console\Input\InputInterface, Symfony\Component\Console\Output\OutputInterface, Symfony\Component\Console\Input\InputOption;
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
      $this->setName('build')
        ->addOption('target', null, InputOption::VALUE_REQUIRED, 'Where do you want Narrative to build the docs?')
        ->addOption('theme', null, InputOption::VALUE_REQUIRED, 'Name of the theme that should be used to build the docs')
        ->setDescription('Build documentation');
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

        $this->prepareTargetPath($target_path, $theme, $output);

        foreach ($project->getStories() as $story) {
          $this->buildStory($target_path, $story, $output);
        }
      } else {
        $output->writeln($project->getPath() . ' is not a valid Narrative project');
      }
    }

    /**
     * Prepare target path
     *
     * @param string $target_path
     * @param Theme $theme
     * @param OutputInterface $output
     * @return bool
     */
    public function prepareTargetPath($target_path, Theme $theme, OutputInterface $output)
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
     * Build single story
     *
     * @param string $target_path
     * @param Story $story
     * @param OutputInterface $output
     */
    private function buildStory($target_path, Story $story, OutputInterface $output)
    {
      $story_target_path = $target_path . '/';

      foreach ($story->getGroups() as $group) {
        $story_target_path .= Narrative::slug($group) . '/';

        if (!is_dir($story_target_path)) {
          Narrative::createDir($story_target_path, function($path) use ($output) {
            $output->writeln("Directory '$path' created");
          });
        }
      }

      $story_target_path .= Narrative::slug($story->getName()) . '.html';

      $this->smarty->assign('current_story', $story);

      Narrative::writeFile($story_target_path, $this->smarty->fetch('story.tpl'), function($path) use (&$output) {
        $output->writeln("File '$path' created");
      });
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