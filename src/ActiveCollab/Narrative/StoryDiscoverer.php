<?php
  namespace ActiveCollab\Narrative;

  /**
   * Project plug-in that helps project discover stories
   *
   * @package ActiveCollab\Narrative
   */
  class StoryDiscoverer
  {
    /**
     * @param string $path
     * @return array
     */
    public function getStoryFiles($path)
    {
      $result = [];

      if(is_dir($path)) {
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {

          /**
           * @var \DirectoryIterator $file
           */
          if(substr($file->getBasename(), 0, 1) != '.' && $file->getExtension() == 'narr') {
            $result[] = $file->getPathname();
          }
        }
      }

      return $result;
    }
  }