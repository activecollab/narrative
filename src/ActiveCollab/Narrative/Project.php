<?php

  namespace ActiveCollab\Narrative;

  /**
   * Narrative project
   *
   * @package Narrative
   */
  final class Project {

    /**
     * @var string
     */
    private $path;

    /**
     * Create a new project instance
     *
     * @param string $path
     */
    function __construct($path) {
      $this->path = $path;
    }

    /**
     * Return project path
     *
     * @return string
     */
    function getPath() {
      return $this->path;
    }

    /**
     * Return all project stories
     *
     * @return Story[]
     */
    function getStories() {
      $result = [];

      if(is_dir("$this->path/stories")) {

        /**
         * @var \DirectoryIterator[] $dir
         */
        $dir = new \DirectoryIterator("$this->path/stories");

        foreach($dir as $file) {
          if($file->isDot() || $file->isDir() || $file->getExtension() != 'narr') {
            continue;
          } // if

          $result[] = new Story($file->getPathname());
        }
      }

      return $result;
    }

    /**
     * Return story by name
     *
     * @param string $name
     * @return Story|null
     */
    function getStory($name) {
      foreach($this->getStories() as $story) {
        if($story->getName() === $name) {
          return $story;
        }
      }

      return null;
    }

    /**
     * Return true if this is a valid project
     *
     * @return bool
     */
    function isValid() {
      return is_dir($this->path);
    }

  }