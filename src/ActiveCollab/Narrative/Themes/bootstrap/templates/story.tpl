<{include "header.tpl"}>

<div id="content_wrapper" class="container">
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

      <div class="text-center">
        <ul class="pagination">
        <{if $previous_story}>
          <li class="prev"><{story story=$previous_story}>&laquo;<{/story}></li>
        <{/if}>
          <li class="top"><a href="#" onclick="window.scrollTo(0, 0); return false;">Top</a></li>
        <{if $next_story}>
          <li class="next"><{story story=$next_story}>&raquo;<{/story}></li>
        <{/if}>
        </ul>
      </div>
    </article>
  </div>
  <{else}>
  <h1>Story Not Found</h1>
  <{/if}>
</div>
</div>

<{include "footer.tpl"}>