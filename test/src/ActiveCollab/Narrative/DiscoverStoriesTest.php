<?php
  namespace ActiveCollab\Narrative\Test;

  use ActiveCollab\Narrative\Project, ActiveCollab\Narrative\StoryDiscoverer;

  /**
   * Test story discovery
   *
   * @package ActiveCollab\Narrative\Test
   */
  class DiscoverStoriesTest extends \PHPUnit_Framework_TestCase
  {
    function testSomething()
    {
      /**
       * @var StoryDiscoverer $mock
       */
      $mock = $this->getMock('\ActiveCollab\Narrative\StoryDiscoverer', [ 'getStoryFiles' ]);
      $mock->method('getStoryFiles')->willReturn([
        '/project/path/stories/Projects/Elements/Tasks/02. Happy Tasks.narr',
        '/project/path/stories/Projects/Elements/Tasks/01. Irresistible Tasks.narr',
        '/project/path/stories/Projects/Elements/Notes/Grumpy Notes.narr',
        '/project/path/stories/Projects/Awesome Projects.narr',
        '/project/path/stories/Getting Started.narr',
      ]);

      $project = new Project('/project/path');
      $project->setStoryDiscoverer($mock);

      $stories = $project->getStories();

      $this->assertEquals('Getting Started', $stories[0]->getName());
      $this->assertEquals([], $stories[0]->getGroups());

      $this->assertEquals('Awesome Projects', $stories[1]->getName());
      $this->assertEquals([ 'Projects' ], $stories[1]->getGroups());

      $this->assertEquals('Grumpy Notes', $stories[2]->getName());
      $this->assertEquals([ 'Projects', 'Elements', 'Notes' ], $stories[2]->getGroups());

      $this->assertEquals('Irresistible Tasks', $stories[3]->getName());
      $this->assertEquals([ 'Projects', 'Elements', 'Tasks' ], $stories[3]->getGroups());

      $this->assertEquals('Happy Tasks', $stories[4]->getName());
      $this->assertEquals([ 'Projects', 'Elements', 'Tasks' ], $stories[4]->getGroups());
    }
  }