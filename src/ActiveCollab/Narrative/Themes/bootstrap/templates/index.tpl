<{include "header.tpl"}>

<div id="content_wrapper" class="container">
  <div id="content">
    <h1><{$project->getName()}></h1>
    <{$project->renderBody() nofilter}>
  </div>
</div>

<{include "footer.tpl"}>