<div class="request">
  <div class="request_and_response_details">
    <div class="response_details">Response: <{$http_code|response_http_code nofilter}>, <{$content_type|response_content_type}><{if $response}> (<a href="#" class="toggle_response" title="Click to show/hide response. Shift+Click to show/hide all responses">Hide</a>)<{/if}></div>
    <div class="request_details"><span class="request_method <{$request->getMethod()}>"><{$request->getMethod()}></span> <span class="request_path"><{$request->getPath()}></span> <{if !$request->executeAsDefaultPersona()}><span class="request_persona">(as <{$request->executeAs()}>)</span><{/if}></div>
  </div>

  <div class="payload_or_query">
  <{if $request->getMethod() && count($request->getQuery())}>
    <p class="block_head">Query Parameters:</p>
    <div class="request_query"><{code inline=false highlight="json"}><{$request->getQuery()|json_encode:$smarty.const.JSON_PRETTY_PRINT nofilter}><{/code}></div>
  <{/if}>

<{if ($request->getMethod() == 'POST' || $request->getMethod() == 'PUT')}>
  <{if $request->getPayload($request_variables)}>
    <p class="block_head">Payload:</p>
    <div class="request_payload"><{code inline=false highlight="json"}><{$request->getPayload($request_variables)|json_encode:$smarty.const.JSON_PRETTY_PRINT nofilter}><{/code}></div>
  <{/if}>
  <{if count($request->getFiles())}>
    <p class="block_head">Files:</p>
    <div class="request_files"><{code inline=false highlight="json"}><{$request->getFiles()|json_encode:$smarty.const.JSON_PRETTY_PRINT nofilter}><{/code}></div>
  <{/if}>
<{/if}>
  </div>

<{if $response}>
  <div class="response">
    <p class="block_head">Response:</p>
  <{if strpos($content_type, 'application/json') !== false}>
    <{code inline=false highlight="json"}><{$response|pretty_printed_json nofilter}><{/code}>
  <{else}>
    <{code inline=false}><{$response}><{/code}>
  <{/if}>
  </div>
<{/if}>
</div>