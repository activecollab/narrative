<{include "header.tpl"}>

<div id="content_wrapper" class="container">
  <{include "story_sidebar.tpl"}>

  <div id="content">
    <{if $current_story}>
    <article>
      <h1><{$current_story->getName()}></h1>
      <div class="body"><{$current_story_body nofilter}></div>
      <div class="comments">
        <{foreach $plugins as $plugin}>
          <{$plugin->renderComments($current_story) nofilter}>
        <{/foreach}>
      </div>
      <{include "prev_top_next.tpl"}>
    </article>
  </div>
  <{else}>
  <h1>Story Not Found</h1>
  <{/if}>
</div>
</div>

<{include "footer.tpl"}>