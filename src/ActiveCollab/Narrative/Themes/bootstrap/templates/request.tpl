<div class="request" role="tabpanel">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#request-<{$request_id}>" aria-controls="request-<{$request_id}>" role="tab" data-toggle="tab">Request</a></li>
    <li role="presentation"><a href="#response-<{$request_id}>" aria-controls="response-<{$request_id}>" role="tab" data-toggle="tab">Response</a></li>
    <li role="presentation"><a href="#curl-<{$request_id}>" aria-controls="curl-<{$request_id}>" role="tab" data-toggle="tab">Curl</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="request-<{$request_id}>">
      <{$request->getMethod()}> <{$request->getPath()}><br>

      <{if $request->getMethod() == 'POST' || $request->getMethod() == 'PUT'}>
        <{code highlight="json"}><{$request->getPayload($request_variables)|json_encode:$smarty.const.JSON_PRETTY_PRINT nofilter}><{/code}>
      <{/if}>
    </div>
    <div role="tabpanel" class="tab-pane" id="response-<{$request_id}>">
      HTTP <{$http_code}> <{$content_type}><br>
      <{if strpos($content_type, 'application/json') !== false}>
        <{code highlight="json"}><{$response|pretty_printed_json nofilter}><{/code}>
      <{else}>
        <{code}><{$response}><{/code}>
      <{/if}>
    </div>
    <div role="tabpanel" class="tab-pane" id="curl-<{$request_id}>">...</div>
  </div>
</div>