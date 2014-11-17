<?php
  namespace ActiveCollab\Narrative;

  use Exception;

  /**
   * Shade theme class
   *
   * @package ActiveCollab\Narrative
   */
  class Theme
  {
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     * @throws Exception
     */
    function __construct($path)
    {
      if (is_dir($path)) {
        $this->path = $path;
      } else {
        throw new Exception("Path '$path' is not a valid Shade theme");
      }
    }

    /**
     * @return string
     */
    function getPath()
    {
      return $this->path;
    }
  }