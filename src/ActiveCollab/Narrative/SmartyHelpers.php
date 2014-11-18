<?php

  namespace ActiveCollab\Narrative;

  use ActiveCollab\Narrative, Smarty, Smarty_Internal_Template, ActiveCollab\Narrative\Error\ParamRequiredError;

  /**
   * Help element text helpers
   *
   * @package ActiveCollab\Narrative
   */
  class SmartyHelpers
  {
    /**
     * @param array $params
     * @return string
     */
    public static function function_stylesheet_url($params)
    {
      $page_level = isset($params['page_level']) ? (integer) $params['page_level'] : 0;
      $locale = isset($params['locale']) && $params['locale'] ? $params['locale'] : null;

      return '<link rel="stylesheet" type="text/css" href="' . self::pageLevelToPrefix($page_level, $locale) . "assets/stylesheets/main.css?timestamp=" . time() . '">';
    }

    /**
     * Render navigation link
     *
     * @param array $params
     * @return string
     */
    public static function function_navigation_link($params)
    {
      $page_level = isset($params['page_level']) && (integer) $params['page_level'] > 0 ? (integer) $params['page_level'] : 0;

      return self::pageLevelToPrefix($page_level) . 'index.html';
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
      $page_level = isset($params['page_level']) ? (integer) $params['page_level'] : 0;
      $current_locale = isset($params['current_locale']) ? $params['current_locale'] : self::$default_locale;

      if (empty($name)) {
        throw new ParamRequiredError('name parameter is required');
      }

      return self::pageLevelToPrefix($page_level, $current_locale) . "assets/$name";
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
     * @param integer $page_level
     * @param string|null $locale
     * @return string
     */
    private static function pageLevelToPrefix($page_level, $locale = null)
    {
      if ($locale && $locale != self::$default_locale) {
        $page_level++;
      }

      if ($page_level > 0) {
        $prefix = './';

        for ($i = 0; $i < $page_level; $i++) {
          $prefix .= '../';
        }

        return $prefix;
      } else {
        return '';
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
     * Get raw JSON string and return it pretty printed
     *
     * @param string $json
     * @return string
     */
    public static function modifier_pretty_printed_json($json)
    {
      return json_encode(json_decode($json, true), JSON_PRETTY_PRINT);
    }
  }
