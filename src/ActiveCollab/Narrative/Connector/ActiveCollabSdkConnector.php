<?php

  namespace ActiveCollab\Narrative\Connector;

  use ActiveCollab\Narrative\Error\ConnectorError;
  use ActiveCollab\SDK\Client as API;
  use ActiveCollab\SDK\Response AS Response;

  /**
   * activeCollab SDK connector
   *
   * @package ActiveCollab\Narrative\Connector
   */
  class ActiveCollabSdkConnector extends Connector {

    /**
     * Construct and configure the connector
     *
     * @param array $parameters
     * @throws ConnectorError
     */
    function __construct(array $parameters) {
      if(isset($parameters['url']) && $parameters['url']) {
        API::setUrl($parameters['url']);
      } else {
        throw new ConnectorError('URL required');
      }

      if(isset($parameters['token']) && $parameters['token']) {
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
     */
    function get($path, $persona = Connector::DEFAULT_PERSONA) {
      API::setKey($this->getPersona($persona)['token']);
      return API::get($path);
    }

    /**
     * Send a POST request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string $persona
     * @return Response
     */
    function post($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA) {
      API::setKey($this->getPersona($persona)['token']);
      return Api::post($path, $params, $attachments);
    }

    /**
     * Send a PUT request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string $persona
     * @return Response
     */
    function put($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA) {
      API::setKey($this->getPersona($persona)['token']);
      return API::put($path, $params, $attachments);
    }

    /**
     * Send a delete command
     *
     * @param string $path
     * @param array|null $params
     * @param string $persona
     * @return Response
     */
    function delete($path, $params = null, $persona = Connector::DEFAULT_PERSONA) {
      API::setKey($this->getPersona($persona)['token']);
      return API::delete($path, $params);
    }

  }