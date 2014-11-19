<{include "header.tpl"}>

<div id="content_wrapper" class="container">
  <div id="content">
    <article>
      <h1><{$project->getName()}></h1>
      <div class="body">
        <p>Welcome</p>

        <ul>
        <{foreach $stories as $story}>
          <li><{story story=$story}><{$story->getFullName()}><{/story}><{if $test_result->isFailedStory($story)}> <span style="color: red">[test failed]</span><{/if}></li>
        <{/foreach}>
        </ul>
      </div>
    </article>
  </div>
</div>
</div>

<{include "footer.tpl"}>