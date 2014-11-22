<?php
  namespace ActiveCollab\Narrative\Connector;

  use ActiveCollab\Narrative\Error\ConnectorError, \Exception;
  use ActiveCollab\SDK\Client as API;
  use ActiveCollab\SDK\Response AS Response;

  /**
   * activeCollab SDK connector
   *
   * @package ActiveCollab\Narrative\Connector
   */
  class ActiveCollabSdk extends Connector
  {
    /**
     * Construct and configure the connector
     *
     * @param array $parameters
     * @throws ConnectorError
     */
    function __construct(array $parameters)
    {
      if (isset($parameters['url']) && $parameters['url']) {
        API::setUrl($parameters['url']);
      } else {
        throw new ConnectorError('URL required');
      }

      if (isset($parameters['api_version']) && $parameters['api_version']) {
        API::setApiVersion($parameters['api_version']);
      }

      if (isset($parameters['token']) && $parameters['token']) {
        $this->addPersona(Connector::DEFAULT_PERSONA, [ 'token' => $parameters['token'] ]);
      } else {
        throw new ConnectorError('URL required');
      }
    }

    /**
     * Send a get request
     *
     * @param string $path
     * @param string $persona
     * @return Response
     * @throws ConnectorError
     */
    function get($path, $persona = Connector::DEFAULT_PERSONA)
    {
      try {
        API::setKey($this->getPersona($persona)['token']);
        return API::get($path);
      } catch (Exception $e) {
        throw new ConnectorError();
      }
    }

    /**
     * Send a POST request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string $persona
     * @return Response
     * @throws ConnectorError
     */
    function post($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA)
    {
      try {
        API::setKey($this->getPersona($persona)['token']);
        return Api::post($path, $params, $attachments);
      } catch (Exception $e) {
        throw new ConnectorError();
      }
    }

    /**
     * Send a PUT request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string $persona
     * @return Response
     * @throws ConnectorError
     */
    function put($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA)
    {
      try {
        API::setKey($this->getPersona($persona)['token']);
        return API::put($path, $params, $attachments);
      } catch (Exception $e) {
        throw new ConnectorError();
      }
    }

    /**
     * Send a delete command
     *
     * @param string $path
     * @param array|null $params
     * @param string $persona
     * @return Response
     * @throws ConnectorError
     */
    function delete($path, $params = null, $persona = Connector::DEFAULT_PERSONA)
    {
      try {
        API::setKey($this->getPersona($persona)['token']);
        return API::delete($path, $params);
      } catch (Exception $e) {
        throw new ConnectorError();
      }
    }

    /**
     * Create a new persona based on a response
     *
     * @param string $name
     * @param Response $response
     * @return string
     * @throws \ActiveCollab\Narrative\Error\ConnectorError
     */
    function addPersonaFromResponse($name, $response)
    {
      $user_id = $this->isUserResponse($response);

      if($user_id !== false) {
        $subscription = $this->post("/users/{$user_id}/api-subscriptions", [ 'client_vendor' => 'ActiveCollab', 'client_name' => 'Narrative' ]);

        if($token = $this->isSubscriptionResponse($subscription)) {
          $this->addPersona($name, [ 'token' => $token ]);
          return $token;
        }
      }

      throw new ConnectorError('Invalid response');
    }

    /**
     * Check if $response is a valid user response and return user ID
     *
     * @param Response $response
     * @return integer|false
     */
    private function isUserResponse($response)
    {
      if($response instanceof Response && $response->isJson()) {
        $json = $response->getJson();

        if(isset($json['single']) && isset($json['single']['id']) && $json['single']['id'] && isset($json['single']['class']) && in_array($json['single']['class'], [ 'Client', 'Subcontractor', 'Member', 'Owner' ])) {
          return $json['single']['id'];
        }
      }

      return false;
    }

    /**
     * Check if $response is a valid subscription response and return subscription token
     *
     * @param Response $response
     * @return integer|false
     */
    private function isSubscriptionResponse($response)
    {
      if($response instanceof Response && $response->isJson()) {
        $json = $response->getJson();

        if(isset($json['single']) && isset($json['single']['class']) && $json['single']['class'] = 'ApiSubscription') {
          return $json['single']['token'];
        }
      }

      return false;
    }
  }