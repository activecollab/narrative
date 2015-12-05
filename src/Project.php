<?php

namespace ActiveCollab\Narrative;

use ActiveCollab\Narrative\Connector\Connector;
use ActiveCollab\Narrative\Connector\GenericConnector;
use ActiveCollab\Narrative\Error\CommandError;
use ActiveCollab\Narrative\Error\ConnectorError;
use ActiveCollab\Narrative\Error\Error;
use ActiveCollab\Narrative\Error\ParseJsonError;
use ActiveCollab\Narrative\Error\ParseError;
use ActiveCollab\Narrative\Error\ThemeNotFoundError;
use ActiveCollab\Narrative\StoryElement\Request;
use ActiveCollab\Narrative\StoryElement\Sleep;
use ActiveCollab\Narrative\StoryElement\Text;
use Smarty;

/**
 * @package ActiveCollab\Narrative\Narrative
 */
final class Project
{
    /**
     * @var string
     */
    private $path;

    /**
     * Configuration data
     *
     * @var array
     */
    private $configuration = [];

    /**
     * Create a new project instance
     *
     * @param string $path
     * @throws |ActiveCollab\Narrative\Narrative\Error\ParseJsonError
     */
    public function __construct($path)
    {
        $this->path = $path;

        if ($this->isValid()) {
            $configuration_json = file_get_contents($this->path . '/project.json');
            $this->configuration = json_decode($configuration_json, true);

            if ($this->configuration === null) {
                throw new ParseJsonError($configuration_json, json_last_error());
            }

            if (empty($this->configuration)) {
                $this->configuration = [];
            }
        }
    }

    /**
     * Return project name
     *
     * @return string
     */
    public function getName()
    {
        return isset($this->configuration['name']) && $this->configuration['name'] ? $this->configuration['name'] : basename($this->path);
    }

    /**
     * Return configuration option
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getConfigurationOption($name, $default = null)
    {
        return isset($this->configuration[ $name ]) && $this->configuration[ $name ] ? $this->configuration[ $name ] : $default;
    }

    /**
     * Return project path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Connector instance
     *
     * @var Connector
     */
    private $connector = false;

    /**
     * @return Connector
     */
    public function &getConnector()
    {
        if ($this->connector === false) {
            list ($connector_class, $connector_settings) = $this->getConnectorSettings();

            if ($connector_class) {
                if (isset($connector_settings['file'])) {
                    require_once $connector_settings['file'];
                }

                if ((new \ReflectionClass($connector_class))->isSubclassOf(Connector::class)) {
                    $this->connector = new $connector_class($connector_settings);
                }
            }
        }

        return $this->connector;
    }

    /**
     * Return connector settings
     *
     * @return array
     */
    private function getConnectorSettings()
    {
        $connector = $this->getConfigurationOption('connector');

        if (is_array($connector) && count($connector) === 1) {
            foreach ($connector as $connector_class => $connector_settings) {
                return [$connector_class, $connector_settings];
            }
        }

        return [GenericConnector::class, null];
    }

    /**
     * @var array
     */
    private $stories = false;

    /**
     * Return all project stories
     *
     * @return Story[]
     */
    public function getStories()
    {
        if ($this->stories === false) {
            $this->stories = [];

            $story_discoverer = $this->getStoryDiscoverer();

            $story_files = $story_discoverer->getStoryFiles("$this->path/stories");

            if (is_array($story_files)) {
                $this->sortStoryFiles($story_files);

                foreach ($story_files as $story_file) {
                    $this->stories[] = new Story($story_file, "$this->path/stories");
                }
            }
        }

        return $this->stories;
    }

    /**
     * @var StoryDiscoverer
     */
    private $story_discoverer;

    /**
     * Return story discoverer instance
     *
     * @return StoryDiscoverer
     */
    public function getStoryDiscoverer()
    {
        if (empty($this->story_discoverer)) {
            $this->story_discoverer = new StoryDiscoverer();
        }

        return $this->story_discoverer;
    }

    /**
     * Set custom story discoverer
     *
     * @param StoryDiscoverer $discoverer
     * @throws Error
     */
    public function setStoryDiscoverer($discoverer)
    {
        if ($discoverer instanceof StoryDiscoverer || $discoverer === null) {
            $this->story_discoverer = $discoverer;
            $this->stories = false; // Reset stories cache
        } else {
            throw new Error('Valid StoryDiscoverer instance or NULL expected');
        }
    }

    /**
     * Sort story files so root stories are above stories organised in groups
     *
     * @param array $story_files
     */
    private function sortStoryFiles(array &$story_files)
    {
        sort($story_files);

        $root_stories = [];

        foreach ($story_files as $k => $story_file) {
            if (dirname($story_file) === "$this->path/stories") {
                $root_stories[] = $story_file;
                unset($story_files[ $k ]);
            }
        }

        $story_files = array_merge($root_stories, $story_files);
    }

    /**
     * Return story by name
     *
     * @param string $name
     * @return Story|null
     */
    public function getStory($name)
    {
        $name = trim($name, '/');

        foreach ($this->getStories() as $story) {
            if ($story->getFullName() == $name) {
                return $story;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return isset($this->configuration['routes']) && is_array($this->configuration['routes']) ? $this->configuration['routes'] : [];
    }

    /**
     * Return route names from path
     *
     * @param string $path
     * @return string
     */
    public function getRouteNameFromPath($path)
    {
        if (isset($this->configuration['routes']) && is_array($this->configuration['routes'])) {
            $path = trim($path, '/');

            foreach (array_reverse($this->configuration['routes']) as $route_name => $route_settings) {
                if (preg_match($route_settings['match'], $path)) {
                    return $route_name;
                }
            }
        }

        return '';
    }

    /**
     * Return true if this is a valid project
     *
     * @return bool
     */
    public function isValid()
    {
        return is_dir($this->path) && is_file($this->path . '/project.json');
    }

    /**
     * Test a group of stories
     *
     * @param Story      $story
     * @param TestResult $test_result
     */
    public function testStory(Story $story, TestResult &$test_result)
    {
        try {
            if ($elements = $story->getElements()) {
                $this->setUp($story, $test_result);

                try {
                    $variables = $this->getDefaultVariables();

                    foreach ($elements as $element) {
                        if ($element instanceof Request) {
                            $element->execute($this, $variables, $test_result);
                        } elseif ($element instanceof Sleep) {
                            $element->execute($this, $test_result);
                        }
                    }
                } catch (ConnectorError $e) {
                    $this->tearDown($test_result); // Make sure that we tear down the environment in case of an error
                    throw $e;
                }

                $this->tearDown($test_result); // Make sure that we tear down the environment after each request
            }
        } catch (ParseError $e) {
            $test_result->parseError($e);
        } catch (ParseJsonError $e) {
            $test_result->parseJsonError($e);
        } catch (\Exception $e) {
            $test_result->requestExecutionError($e);
        }
    }

    /**
     * Test a group of stories
     *
     * @param Story      $story
     * @param TestResult $test_result
     * @param Smarty     $smarty
     * @return string
     */
    public function testAndRenderStory(Story $story, TestResult &$test_result, Smarty &$smarty)
    {
        $result = '';

        try {
            if ($elements = $story->getElements()) {
                $this->setUp($story, $test_result);

                try {
                    $variables = $this->getDefaultVariables();

                    $request_template = $smarty->createTemplate('request.tpl');
                    $request_id = 1;

                    foreach ($elements as $element) {
                        if ($element instanceof Request) {
                            if ($element->isPreparation()) {
                                $element->execute($this, $variables, $test_result);
                            } else {
                                list($http_code, $content_type, $response) = $element->execute($this, $variables, $test_result);

                                $request_template->assign([
                                'request' => $element,
                                'request_id' => $request_id++,
                                'http_code' => $http_code,
                                'content_type' => $content_type,
                                'response' => $response,
                                ]);

                                $request_template->assignByRef('request_variables', $variables);

                                $result .= $request_template->fetch();
                            }
                        } elseif ($element instanceof Sleep) {
                            $element->execute($this, $test_result);

                            $result .= '<div class="sleep">Sleeping for ' . $element->howLong() . ' seconds</div>';
                        } elseif ($element instanceof Text) {
                            $result .= $this->renderText($element, $smarty);
                        }
                    }

                    if (count($personas = $this->getConnector()->getPersonas()) > 1) {
                        $persona_names = [];

                        foreach ($personas as $persona => $persona_settings) {
                            if ($persona === Connector::DEFAULT_PERSONA) {
                                continue;
                            }
                            $persona_names[] = $persona;
                        }

                        $result = '<p class="personas">Personas in this Story: Default, ' . implode(', ', $persona_names) . '.</p>' . $result;
                    }
                } catch (ConnectorError $e) {
                    $this->tearDown($test_result); // Make sure that we tear down the environment in case of an error
                    throw $e;
                }

                $this->tearDown($test_result); // Make sure that we tear down the environment after each request
            }
        } catch (ParseError $e) {
            $test_result->parseError($e);
        } catch (ParseJsonError $e) {
            $test_result->parseJsonError($e);
        } catch (\Exception $e) {
            $test_result->requestExecutionError($e);
        }

        return $result;
    }

    /**
     * @param  Text   $element
     * @param  Smarty $smarty
     * @return string
     */
    private function renderText(Text $element, Smarty &$smarty)
    {
        if (strpos($element->getSource(), '<{') === false) {
            return Narrative::markdownToHtml($element->getSource());
        } else {
            return Narrative::markdownToHtml($smarty->createTemplate('eval:' . $element->getSource())->fetch());
        }
    }

    /**
     * Return variables that are available by default in stories
     *
     * @return array
     */
    private function getDefaultVariables()
    {
        date_default_timezone_set('UTC');

        return [
            'now' => date('Y-m-d H:i:s'),
            'now_timestamp' => time(),
            'today' => date('Y-m-d'),
            'today_timestamp' => strtotime(date('Y-m-d')),
            'tomorrow' => date('Y-m-d', strtotime('+1 day')),
            'tomorrow_timestamp' => strtotime(date('Y-m-d', strtotime('+1 day'))),
        ];
    }

    // ---------------------------------------------------
    //  Set up and tear down
    // ---------------------------------------------------

    /**
     * Set up the environment before each story
     *
     * @param Story      $story
     * @param TestResult $test_result
     */
    public function setUp(Story $story, TestResult &$test_result)
    {
        $test_result->storySetUp($story);

        if (isset($this->configuration['set_up']) && is_array($this->configuration['set_up'])) {
            foreach ($this->configuration['set_up'] as $command) {
                $this->executeCommand($command);
            }
        }
    }

    /**
     * Tear down before each story
     *
     * @param TestResult $test_result
     */
    public function tearDown(TestResult &$test_result)
    {
        $test_result->storyTearDown();

        $this->getConnector()->forgetNonDefaultPersonas();

        if (isset($this->configuration['tear_down']) && is_array($this->configuration['tear_down'])) {
            foreach ($this->configuration['tear_down'] as $command) {
                $this->executeCommand($command);
            }
        }
    }

    /**
     * Execute set up or tear down command
     *
     * @param  array|string $c
     * @return string
     * @throws CommandError
     */
    private function executeCommand($c)
    {
        $original_working_dir = getcwd();

        if (is_string($c)) {
            $command = $c;
        } elseif (is_array($c) && count($c)) {
            list ($command, $working_dir) = $c;

            if ($working_dir != $original_working_dir) {
                chdir($working_dir);
            }
        } else {
            throw new CommandError($c);
        }

        $output = [];
        $exit_code = 0;

        exec($command, $output, $exit_code);

        if ($exit_code !== 0) {
            throw new CommandError($command, "Command exited with code $exit_code. Output: " . implode("\n", $output));
        }

        print "\n";
        print "Command: $command\n";
        print "Command output:\n\n";
        print implode("\n", $output);
        print "\n\n";

        if (getcwd() != $original_working_dir) {
            chdir($original_working_dir);
        }

        return implode("\n", $output);
    }

    /**
     * Return major API version number
     *
     * @return int
     */
    public function getApiVersion()
    {
        return (integer)$this->getConfigurationOption('api_version', 1);
    }

    /**
     * Return temp path
     *
     * @return string
     */
    public function getTempPath()
    {
        return $this->path . '/temp';
    }

    /**
     * @var string
     */
    private $default_build_target;

    /**
     * Return default build target
     *
     * @return string|null
     */
    function getDefaultBuildTarget()
    {
        if (empty($this->default_build_target)) {
            $this->default_build_target = $this->getConfigurationOption('default_build_target');

            if (empty($this->default_build_target)) {
                $this->default_build_target = "$this->path/build";
            }
        }

        return $this->default_build_target;
    }

    /**
     * Return build theme
     *
     * @param string|null $name
     * @return Theme
     * @throws ThemeNotFoundError
     */
    function getBuildTheme($name = null)
    {
        if ($name) {
            $theme_path = __DIR__ . "/Themes/$name"; // Input
        } elseif (is_dir($this->getPath() . '/theme')) {
            $theme_path = $this->getPath() . '/theme'; // Project specific theme
        } else {
            $theme_path = __DIR__ . "/Themes/" . $this->getDefaultBuildTheme(); // Default built in theme
        }

        if ($theme_path && is_dir($theme_path)) {
            return new Theme($theme_path);
        } else {
            throw new ThemeNotFoundError($name, $theme_path);
        }
    }

    /**
     * Return name of the default build theme
     *
     * @return string
     */
    function getDefaultBuildTheme()
    {
        return $this->getConfigurationOption('default_build_theme', 'bootstrap');
    }

    /**
     * @var array
     */
    private $social_links = false;

    /**
     * @return array
     */
    function getSocialLinks()
    {
        if ($this->social_links === false) {
            $this->social_links = [];

            if (is_array($this->getConfigurationOption('social_links'))) {
                foreach ($this->getConfigurationOption('social_links') as $service => $handle) {
                    switch ($service) {
                        case 'twitter':
                            $this->social_links[ $service ] = ['name' => 'Twitter', 'url' => "https://twitter.com/{$handle}", 'icon' => "images/icon_{$service}.png"];
                            break;
                        case 'facebook':
                            $this->social_links[ $service ] = ['name' => 'Facebook', 'url' => "https://www.facebook.com/{$handle}", 'icon' => "images/icon_{$service}.png"];
                            break;
                        case 'google':
                            $this->social_links[ $service ] = ['name' => 'Google+', 'url' => "https://plus.google.com/+{$handle}", 'icon' => "images/icon_{$service}.png"];
                            break;
                    }
                }
            }
        }

        return $this->social_links;
    }
}
