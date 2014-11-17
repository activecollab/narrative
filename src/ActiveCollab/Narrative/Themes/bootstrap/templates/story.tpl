<{include "header.tpl"}>

<div id="content_wrapper" class="container">
  <{include "book_sidebar.tpl"}>

  <div id="content">
    <{if $current_page}>
    <article>
      <h1><{$current_page->getTitle()}></h1>
      <div class="body"><{$current_page->renderBody() nofilter}></div>
      <div class="comments">
        <{foreach $plugins as $plugin}>
          <{$plugin->renderComments($current_page) nofilter}>
        <{/foreach}>
      </div>
      <{include "prev_top_next.tpl"}>
    </article>
  </div>
  <{else}>
  <h1>Book Page Not Found</h1>
  <{/if}>
</div>
</div>

<{include "footer.tpl"}>