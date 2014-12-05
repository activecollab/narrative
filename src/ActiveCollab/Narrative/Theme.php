<?php
  namespace ActiveCollab\Narrative;

  use Exception;

  /**
   * Narrative theme
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
        throw new Exception("Path '$path' is not a valid Narrative theme");
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