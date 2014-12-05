<div class="container" id="footer">
  <div class="copyright"><p>&copy; <{if $copyright_since}><{$copyright_since}>-<{/if}><{'Y'|date}> &middot; <{$copyright}>, All rights reserved. Built with <a href="https://www.activecollab.com/labs/narrative" title="Narrative builds help portals from Markdown files">Narrative v<{narrative_version}></a>.</p></div>

  <div class="social">
    <p>Stay up to date with all new features:</p>
    <ul class="links">
    <{foreach $project->getSocialLinks() as $service}>
      <li><a href="<{$service.url}>" target="_blank"><img  src="<{theme_asset name=$service.icon page_level=$page_level current_locale=$current_locale}>" title="{$service.name}" alt="{$service.name} icon"></a></li>
    <{/foreach}>
    </ul>
  </div>
</div>

<{foreach $plugins as $plugin}>
  <{$plugin->renderFoot() nofilter}>
<{/foreach}>