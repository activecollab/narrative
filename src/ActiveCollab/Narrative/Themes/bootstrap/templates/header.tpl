<!DOCTYPE HTML>
<html lang="<{$current_locale}>">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><{$project->getName()}></title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
  <![endif]-->

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>

  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

  <{stylesheet_url page_level=$page_level locale=$current_locale}>

  <{foreach $plugins as $plugin}>
    <{$plugin->renderHead() nofilter}>
  <{/foreach}>
</head>

<body>
  <{foreach $plugins as $plugin}>
    <{$plugin->renderBody() nofilter}>
  <{/foreach}>

  <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="<{navigation_link page_level=$page_level}>"><{$project->getName()}></a>
      </div>

      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
          <li<{if $current_section == 'whats_new'}> class="active"<{/if}>><a href="<{navigation_link section=whats_new page_level=$page_level}>">What's New?</a></li>
          <li<{if $current_section == 'releases'}> class="active"<{/if}>><a href="<{navigation_link section=releases page_level=$page_level}>">Release Notes</a></li>
          <li<{if $current_section == 'books'}> class="active"<{/if}>><a href="<{navigation_link section=books page_level=$page_level}>">Manuals &amp; Guides</a></li>
        <{if $project->getVideos()->count()}>
          <li<{if $current_section == 'videos'}> class="active"<{/if}>><a href="<{navigation_link section=videos page_level=$page_level}>">Videos</a></li>
        <{/if}>
        </ul>

        <{if count($project->getLocales()) > 1}>
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><{$current_locale|strtoupper}> <span class="caret"></span></a>
            <ul class="dropdown-menu" role="menu">
              <{foreach $project->getLocales() as $code => $name}>
              <li><a href="<{locale_link locale=$code default_locale=$default_locale current_locale=$current_locale page_level=$page_level}>"><{$name}></a></li>
              <{/foreach}>
            </ul>
          </li>
        </ul>
        <{/if}>
        <!-- <form class="navbar-form navbar-right" role="search">
          <div class="form-group">
            <input type="text" class="form-control" placeholder="Search">
          </div>
        </form> -->
      </div>
    </div>
  </nav>

