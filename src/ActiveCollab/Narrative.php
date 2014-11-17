<?php
  namespace ActiveCollab;

  use ActiveCollab\Narrative\Project, ActiveCollab\Narrative\Error\Error, ActiveCollab\Narrative\Error\TempNotFoundError, Michelf\MarkdownExtra, URLify, Hyperlight\Hyperlight, ActiveCollab\Narrative\SmartyHelpers, Smarty, ReflectionClass, ReflectionMethod;
  use ActiveCollab\Narrative\Theme;

  /**
   * Main class for interaction with Narrative projects
   */
  final class Narrative
  {
    const VERSION = '0.9.0';

    /**
     * @var Smarty
     */
    private static $smarty = false;

    /**
     * Initialize Smarty
     *
     * @param Project $project
     * @param Theme $theme
     * @return Smarty
     * @throws TempNotFoundError
     * @throws \SmartyException
     */
    public static function &initSmarty(Project &$project, Theme $theme)
    {
      if (self::$smarty === false) {
        self::$smarty = new Smarty();

        $temp_path = $project->getTempPath();

        if (is_dir($temp_path)) {
          self::$smarty->setCompileDir($temp_path);
        } else {
          throw new TempNotFoundError($temp_path);
        }

        self::$smarty->setTemplateDir($theme->getPath() . '/templates');
        self::$smarty->compile_check = true;
        self::$smarty->left_delimiter = '<{';
        self::$smarty->right_delimiter = '}>';
        self::$smarty->registerFilter('variable', '\ActiveCollab\Narrative::clean'); // {$foo nofilter}

        $helper_class = new ReflectionClass('\ActiveCollab\Narrative\SmartyHelpers');

        foreach ($helper_class->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC) as $method) {
          $method_name = $method->getName();

          if (substr($method_name, 0, 6) === 'block_') {
            self::$smarty->registerPlugin('block', substr($method_name, 6), ['\ActiveCollab\Narrative\SmartyHelpers', $method_name]);
          } elseif (substr($method_name, 0, 9) === 'function_') {
            self::$smarty->registerPlugin('function', substr($method_name, 9), ['\ActiveCollab\Narrative\SmartyHelpers', $method_name]);
          };
        }

        self::$smarty->assign([ 'project' => $project ]);
      }

      return self::$smarty;
    }

    /**
     * Return prepared Smarty instance
     *
     * @return Smarty
     */
    public static function &getSmarty()
    {
      return self::$smarty;
    }

    /**
     * @param string $markdown
     * @return string
     */
    public static function markdownToHtml($markdown)
    {
      return MarkdownExtra::defaultTransform($markdown);
    }

    /**
     * Renders the full preview with line numbers and all necessary DOM
     *
     * @param string $content
     * @param string $syntax
     * @return string
     */
    public static function highlightCode($content, $syntax)
    {
      $content = trim($content);

      if ($syntax) {
        $hyperlight = new Hyperlight(strtolower($syntax));
        $content = $hyperlight->render($content);
      } else {
        $content = self::clean($content);
      }

      $number_of_lines = count(explode("\n", $content));

      $output = '<div class="syntax_higlighted source-code">';
      $output .= '<div class="syntax_higlighted_line_numbers lines"><pre>' . implode("\n", range(1, $number_of_lines)) . '</pre></div>';
      $output .= '<div class="syntax_higlighted_source"><pre>' . $content . '</pre></div>';
      $output .= '</div>';

      return $output;
    }

    /**
     * Return slug from string
     *
     * @static
     * @param $string
     * @param string $space
     * @return mixed|string
     */
    public static function slug($string, $space = '-')
    {
      $string = URLify::transliterate($string);

      $string = preg_replace("/[^a-zA-Z0-9 -]/", '', $string);
      $string = strtolower($string);
      $string = str_replace(" ", $space, $string);

      while (strpos($string, '--') !== false) {
        $string = str_replace('--', '-', $string);
      }

      return trim($string);
    }

    /**
     * Equivalent to htmlspecialchars(), but allows &#[0-9]+ (for unicode)
     *
     * @param string $str
     * @return string
     * @throws Error
     */
    public static function clean($str)
    {
      if (is_scalar($str)) {
        $str = preg_replace('/&(?!#(?:[0-9]+|x[0-9A-F]+);?)/si', '&amp;', $str);
        $str = str_replace(['<', '>', '"'], ['&lt;', '&gt;', '&quot;'], $str);

        return $str;
      } elseif ($str === null) {
        return '';
      } else {
        throw new Error('Input needs to be scalar value');
      }
    }

    /**
     * Open HTML tag
     *
     * @param  string $name
     * @param  array $attributes
     * @param  callable|string|null $content
     * @return string
     */
    public static function htmlTag($name, $attributes = null, $content = null)
    {
      if ($attributes) {
        $result = "<$name";

        foreach ($attributes as $k => $v) {
          if ($k) {
            if (is_bool($v)) {
              if ($v) {
                $result .= " $k";
              }
            } else {
              $result .= ' ' . $k . '="' . ($v ? self::clean($v) : $v) . '"';
            }
          }
        }

        $result .= '>';
      } else {
        $result = "<$name>";
      }

      if ($content) {
        if (is_callable($content)) {
          $result .= call_user_func($content);
        } else {
          $result .= $content;
        }

        $result .= "</$name>";
      }

      return $result;
    }
  }