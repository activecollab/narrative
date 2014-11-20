<?php

  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative, Smarty, Smarty_Internal_Template, ActiveCollab\Narrative\Error\Error, ActiveCollab\Narrative\Error\ParamRequiredError;

  /**
   * Help element text helpers
   *
   * @package ActiveCollab\Narrative
   */
  class SmartyHelpers
  {
    /**
     * @var Project
     */
    private static $current_project;

    /**
     * Return current project
     *
     * @return Project
     */
    public static function &getCurrentProject()
    {
      return self::$current_project;
    }

    /**
     * Set current project
     *
     * @param Project|null $project
     * @throws Error
     */
    public static function setCurrentProject($project)
    {
      if ($project instanceof Project || $project === null) {
        self::$current_project = $project;
      } else {
        throw new Error('Project instance or NULL expected');
      }
    }

    /**
     * @var Story
     */
    private static $current_story;

    /**
     * Return current story
     *
     * @return Story
     */
    public static function &getCurrentStory()
    {
      return self::$current_story;
    }

    /**
     * Set current story
     *
     * @param Story|null $story
     * @throws Error
     */
    public static function setCurrentStory($story)
    {
      if ($story instanceof Story || $story === null) {
        self::$current_story = $story;
      } else {
        throw new Error('Story instance or NULL expected');
      }
    }

    // ---------------------------------------------------
    //  Links
    // ---------------------------------------------------

    /**
     * @return string
     */
    public static function function_stylesheet_url()
    {
      return '<link rel="stylesheet" type="text/css" href="' . self::getCurrentPagePrefix() . "assets/stylesheets/main.css?timestamp=" . time() . '">';
    }

    /**
     * Render navigation link
     *
     * @param array $params
     * @return string
     */
    public static function function_navigation_link($params)
    {
      if (isset($params['section'])) {
        if ($params['section'] === 'stories') {
          return self::getCurrentPagePrefix() . 'stories.html';
        } elseif ($params['section'] == 'routes') {
          return self::getCurrentPagePrefix() . 'routes.html';
        }
      }

      return self::getCurrentPagePrefix() . 'index.html';
    }

    /**
     * Return theme param URl
     *
     * @param array $params
     * @return string
     * @throws ParamRequiredError
     */
    public static function function_theme_asset($params)
    {
      $name = isset($params['name']) && $params['name'] ? ltrim($params['name'], '/') : null;

      if (empty($name)) {
        throw new ParamRequiredError('name parameter is required');
      }

      return self::getCurrentPagePrefix() . "assets/$name";
    }

    /**
     * Image function
     *
     * @param  array  $params
     * @param   Smarty_Internal_Template $smarty
     * @return string
     * @throws ParamRequiredError
     */
    public static function function_image($params, Smarty_Internal_Template &$smarty)
    {
      if (isset($params['name']) && $params['name']) {
//        $page_level = self::$current_element->getPageLevel();
//
//        if (self::$current_element instanceof BookPage) {
//          $params = [ 'src' => self::getBookPageImageUrl($params['name'], $page_level) ];
//        } elseif (self::$current_element instanceof WhatsNewArticle) {
//          $params = [ 'src' => self::getWhatsNewArticleImageUrl($params['name'], $page_level) ];
//        } else {
//          return '#';
//        }

        return '<div class="center">' . Narrative::htmlTag('img', $params) . '</div>';
      } else {
        throw new ParamRequiredError('name');
      }
    }

    /**
     * Link to a story
     *
     * @param  array       $params
     * @param  string      $content
     * @param  Smarty      $smarty
     * @param  boolean     $repeat
     * @return string|null
     * @throws ParamRequiredError
     */
    public static function block_story($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      if (isset($params['story']) && $params['story'] instanceof Story) {
        $story = $params['story'];
        unset($params['story']);
      } else {
        if (isset($params['name']) && $params['name']) {
          $story = self::getCurrentProject()->getStory($params['name']);
        } else {
          throw new ParamRequiredError('name');
        }
      }

      if ($story instanceof Story) {
        if (isset($params['section']) && $params['section']) {
          $section = '#s-' . Narrative::slug($params['section']);
          unset($params['section']);
        } else {
          $section = '';
        }

        $params['href'] = self::getStoryUrl($story) . $section;

        if (empty($params['class'])) {
          $params['class'] = 'link_to_story';
        } else {
          $params['class'] .= ' link_to_story';
        }

        $params['data-story-name'] = $story->getName();

        return Narrative::htmlTag('a', $params, $content);
      } else {
        $development_error_message = 'Story not found';
      }

      if (Narrative::isTesting() && isset($development_error_message)) {
        return '<span style="color: red; border-bottom: 1px dotted red; cursor: help;" title="Invalid story link: ' . Narrative::clean($development_error_message) . '">' . $content . '</span>';
      } else {
        return $content;
      }
    }

    /**
     * Return story URL relative to the current story
     *
     * @param Story $story
     * @return string
     */
    static private function getStoryUrl(Story $story)
    {
      $url = self::getCurrentPagePrefix();

      foreach (array_merge([ 'v' . self::$current_project->getApiVersion() ], $story->getGroups()) as $group) {
        $url .= Narrative::slug($group) . '/';
      }

      return $url . Narrative::slug($story->getName()) . '.html';
    }

    /**
     * Return current story level
     *
     * @return int
     */
    static private function getCurrentStoryLevel()
    {
      if (self::$current_story instanceof Story) {
        if (is_array(self::$current_story->getGroups())) {
          return count(self::$current_story->getGroups()) + 1;
        } else {
          return 1;
        }
      } else {
        return 0;
      }
    }

    /**
     * Return current page prefix
     *
     * @return string
     */
    static private function getCurrentPagePrefix()
    {
      $current_story_level = self::getCurrentStoryLevel();

      $url = '';

      for ($i = 0; $i < $current_story_level; $i++) {
        $url .= '../';
      }

      return $url;
    }

    /**
     * Render page content
     *
     * @param array $params
     * @param Smarty_Internal_Template $smarty
     * @return string
     * @throws ParamRequiredError
     */
    public static function function_page($params, &$smarty)
    {
      if (isset($params['name']) && $params['name']) {
        $story_path = self::$current_project->getPath() . '/stories/' . $params['name'] . '.md';

        if (is_file($story_path)) {
          $content = file_get_contents($story_path);

          if (strpos($content, '<{') === false) {
            return Narrative::markdownToHtml($content);
          } else {
            return Narrative::markdownToHtml($smarty->fetch('eval:' . $content));
          }
        }

        return '';
      } else {
        throw new ParamRequiredError('name');
      }
    }

    /**
     * Note block
     *
     * @param  array   $params
     * @param  string  $content
     * @param  Smarty  $smarty
     * @param  boolean $repeat
     * @return string
     */
    public static function block_note($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      $title = isset($params['title']) && $params['title'] ? $params['title'] : null;

      if (empty($title)) {
        $title = 'Note';
      }

      return '<div class="note panel panel-warning"><div class="panel-heading">' . Narrative::clean($title) . '</div><div class="panel-body">' . Narrative::markdownToHtml(trim($content)) . '</div></div>';
    }

    /**
     * Option block
     *
     * @param  array   $params
     * @param  string  $content
     * @param  Smarty  $smarty
     * @param  boolean $repeat
     * @return string
     */
    public static function block_option($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      if (empty($params['class'])) {
        $params['class'] = 'outlined_inline option';
      } else {
        $params['class'] .= ' outlined_inline option';
      }

      return Narrative::htmlTag('span', $params, function () use ($content) {
        return Narrative::clean(trim($content));
      });
    }

    /**
     * Term block
     *
     * @param  array   $params
     * @param  string  $content
     * @param  Smarty  $smarty
     * @param  boolean $repeat
     * @return string
     */
    public static function block_term($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      if (empty($params['class'])) {
        $params['class'] = 'outlined_inline term';
      } else {
        $params['class'] .= ' outlined_inline term';
      }

      return Narrative::htmlTag('span', $params, function () use ($content) {
        return Narrative::clean(trim($content));
      });
    }

    /**
     * Wrap file system paths using this block
     *
     * @param  array   $params
     * @param  string  $content
     * @param  Smarty  $smarty
     * @param  boolean $repeat
     * @return string
     */
    public static function block_path($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      if (empty($params['class'])) {
        $params['class'] = 'outlined_inline outlined_inline_mono path';
      } else {
        $params['class'] .= ' outlined_inline outlined_inline_mono path';
      }

      return Narrative::htmlTag('span', $params, function () use ($content) {
        return Narrative::clean(trim($content));
      });
    }

    /**
     * Wrap API commands using this block
     *
     * @param  array   $params
     * @param  string  $content
     * @param  Smarty  $smarty
     * @param  boolean $repeat
     * @return string
     */
    public static function block_command($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      if (empty($params['class'])) {
        $params['class'] = 'outlined_inline outlined_inline_mono path';
      } else {
        $params['class'] .= ' outlined_inline outlined_inline_mono path';
      }

      return Narrative::htmlTag('span', $params, function () use ($content) {
        return Narrative::clean(trim($content));
      });
    }

    /**
     * Code block
     *
     * @param  array   $params
     * @param  string  $content
     * @param  Smarty  $smarty
     * @param  boolean $repeat
     * @return string
     */
    public static function block_code($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      $content = trim($content); // Remove whitespace

      if (array_key_exists('inline', $params)) {
        $inline = isset($params['inline']) && $params['inline'];
      } else {
        $inline = strpos($content, "\n") === false;
      }

      if ($inline) {
        if (empty($params['class'])) {
          $params['class'] = 'outlined_inline outlined_inline_mono inline_code';
        } else {
          $params['class'] .= ' outlined_inline outlined_inline_mono inline_code';
        }

        return Narrative::htmlTag('span', $params, function () use ($content) {
          return Narrative::clean(trim($content));
        });
      } else {
        $highlight = isset($params['highlight']) && $params['highlight'] ? $params['highlight'] : null;

        if ($highlight === 'php') {
          $highlight = 'iphp';
        }

        if ($highlight === 'html' || $highlight === 'xhtml') {
          $highlight = 'xml';
        }

        if ($highlight === 'json') {
          $highlight = 'javascript';
        }

        return Narrative::highlightCode($content, $highlight);
      }
    }

    /**
     * Render a page sub-header
     *
     * @param  array   $params
     * @param  string  $content
     * @param  Smarty  $smarty
     * @param  boolean $repeat
     * @return string
     */
    public static function block_sub($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      $slug = isset($params['slug']) ? $params['slug'] : null;

      if (empty($slug)) {
        $slug = Narrative::slug($content);
      }

      return '<h3 id="s-' . Narrative::clean($slug) . '" class="sub_header">' . Narrative::clean($content) . ' <a href="#s-' . Narrative::clean($slug) . '" title="Link to this Section" class="sub_permalink">#</a></h3>';
    }

    /**
     * Render a tutorial step
     *
     * @param  array   $params
     * @param  string  $content
     * @param  Smarty  $smarty
     * @param  boolean $repeat
     * @return string
     */
    public static function block_step($params, $content, &$smarty, &$repeat)
    {
      if ($repeat) {
        return null;
      }

      $num = isset($params['num']) ? (integer) $params['num'] : null;

      if (empty($num)) {
        $num = 1;
      }

      return '<div class="step step-' . $num . '">
        <div class="step_num"><span>' . $num . '</span></div>
        <div class="step_content">' . Narrative::markdownToHtml(trim($content)) . '</div>
      </div>';
    }

    /**
     * Return Narrative version
     */
    public static function function_narrative_version()
    {
      return Narrative::VERSION;
    }

    /**
     * @return string
     */
    public static function function_GET()
    {
      return self::renderMethod('GET');
    }

    /**
     * @return string
     */
    public static function function_POST()
    {
      return self::renderMethod('POST');
    }

    /**
     * @return string
     */
    public static function function_PUT()
    {
      return self::renderMethod('PUT');
    }

    /**
     * @return string
     */
    public static function function_DELETE()
    {
      return self::renderMethod('DELETE');
    }

    /**
     * @param string $method
     * @return string
     */
    private static function renderMethod($method)
    {
      return '<span class="request_method ' . $method . '">' . $method . '</span>';
    }

    /**
     * Get raw JSON string and return it pretty printed
     *
     * @param string $json
     * @return string
     */
    public static function modifier_pretty_printed_json($json)
    {
      return json_encode(json_decode($json, true), JSON_PRETTY_PRINT);
    }

    /**
     * HTTP statuses
     *
     * @var array
     */
    private static $http_statuses = [
      100 => "Continue",
      101 => "Switching Protocols",
      200 => "OK",
      201 => "Created",
      202 => "Accepted",
      203 => "Non-Authoritative Information",
      204 => "No Content",
      205 => "Reset Content",
      206 => "Partial Content",
      300 => "Multiple Choices",
      301 => "Moved Permanently",
      302 => "Found",
      303 => "See Other",
      304 => "Not Modified",
      305 => "Use Proxy",
      307 => "Temporary Redirect",
      400 => "Bad Request",
      401 => "Unauthorized",
      402 => "Payment Required",
      403 => "Forbidden",
      404 => "Not Found",
      405 => "Method Not Allowed",
      406 => "Not Acceptable",
      407 => "Proxy Authentication Required",
      408 => "Request Time-out",
      409 => "Conflict",
      410 => "Gone",
      411 => "Length Required",
      412 => "Precondition Failed",
      413 => "Request Entity Too Large",
      414 => "Request-URI Too Large",
      415 => "Unsupported Media Type",
      416 => "Requested range not satisfiable",
      417 => "Expectation Failed",
      500 => "Internal Server Error",
      501 => "Not Implemented",
      502 => "Bad Gateway",
      503 => "Service Unavailable",
      504 => "Gateway Time-out"
    ];

    /**
     * Format response code
     *
     * @param integer $http_code
     * @return string
     */
    public static function modifier_response_http_code($http_code)
    {
      $class = (integer) floor($http_code / 100) === 2 ? 'ok' : 'error';
      $message = isset(self::$http_statuses[$http_code]) ? self::$http_statuses[$http_code] : 'Unknown';

      return '<span class="http_code ' . $class . '" title="' . $message . '">HTTP ' . $http_code . '</span>';
    }

    /**
     * Format response content type
     *
     * @param string $content_type
     * @return string
     */
    public static function modifier_response_content_type($content_type)
    {
      return trim(array_shift(explode(';', $content_type)));
    }

    /**
     * Format response content type
     *
     * @param Story|null $story
     * @return integer
     */
    public static function modifier_story_level($story)
    {
      return $story instanceof Story ? count($story->getGroups()) : 0;
    }

    /**
     * @return string
     */
    public static function function_stories_index()
    {
      $result = '<ul>';

      foreach (self::$current_project->getStories() as $story) {
        $groups = $story->getGroups();

        if (is_array($groups) && count($groups)) {
          $result .= '<li><a href="' . self::getStoryUrl($story) . '">' . Narrative::clean(implode(' / ', $groups)) . ' / ' . Narrative::clean($story->getName()) . ' </a>';
        } else {
          $result .= '<li><a href="' . self::getStoryUrl($story) . '">' . Narrative::clean($story->getName()) . '</a>';
        }
      }

      return $result . '</ul>';
    }
  }