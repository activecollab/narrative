<div id="sidebar">
  <p>Table of Contents:</p>
  <ol>
<{foreach $pages as $page}>
  <li class="<{if $current_page && $current_page->getShortName() == $page->getShortName()}>selected<{/if}>"><a href="<{$page->getShortName()}>.html"><{$page->getTitle()}></a></li>
<{/foreach}>
  </ol>
</div>