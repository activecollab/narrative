<{include "header.tpl"}>

<div id="content_wrapper" class="container">
  <div id="content">
    <article>
      <div class="body">
        <{page name='index'}>

        <h2>Stories</h2>
        <ol>
        <{foreach $stories as $story}>
          <li><{story story=$story}><{$story->getFullName()}><{/story}><{if $test_result->isFailedStory($story)}> <span style="color: red">[test failed]</span><{/if}></li>
        <{/foreach}>
        </ol>
      </div>
    </article>
  </div>
</div>

<{include "footer.tpl"}>