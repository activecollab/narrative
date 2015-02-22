<?php

  namespace ActiveCollab\Narrative\Connector;

  use ActiveCollab\Narrative\Error\ConnectorError, ActiveCollab\Narrative\ConnectorResponse, Guzzle\Http\Client, \Exception;

  /**
   * Generic connector that makes HTTP requests
   *
   * @package ActiveCollab\Narrative\Connector
   */
  final class Generic extends Connector
  {
    /**
     * @var Client
     */
    private $client;

    private $url = 'http://localhost:8000/index.php';

    /**
     * Construct and configure the connector
     *
     * @param array $parameters
     * @throws ConnectorError
     */
    function __construct(array $parameters)
    {
      if (isset($parameters['url']) && $parameters['url']) {
        $this->url = $parameters['url'];
      }

      $this->client = new Client();
    }

    /**
     * Send a get request
     *
     * @param string $path
     * @param string $persona
     * @return ConnectorResponse
     * @throws ConnectorError
     */
    function get($path, $persona = Connector::DEFAULT_PERSONA)
    {
      try {
        if (substr($path, 0, 1) == '/') {
          $path = substr($path, 1);
        }

        return $this->client->get("{$this->url}/{$path}");
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
     * @return ConnectorResponse
     * @throws ConnectorError
     */
    function post($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA)
    {
      try {
        return $this->sdkResponseToConnectorResponse(Api::post($path, $params, $attachments));
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
     * @return ConnectorResponse
     * @throws ConnectorError
     */
    function put($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA)
    {
      try {
        return $this->sdkResponseToConnectorResponse(API::put($path, $params, $attachments));
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
     * @return ConnectorResponse
     * @throws ConnectorError
     */
    function delete($path, $params = null, $persona = Connector::DEFAULT_PERSONA)
    {
      try {
        return $this->sdkResponseToConnectorResponse(API::delete($path, $params));
      } catch (Exception $e) {
        throw new ConnectorError();
      }
    }

    /**
     * @param  $sdk_response
     * @return ConnectorResponse
     */
    private function clientResponseToConnectorResponse($sdk_response)
    {
      if ($sdk_response instanceof SdkResponse) {
        return new ConnectorResponse($sdk_response->getHttpCode(), $sdk_response->getContentType(), $sdk_response->getContentLenght(), $sdk_response->getBody(), $sdk_response->getTotalTime());
      }

      return $sdk_response;
    }

    /**
     * Create a new persona based on a response
     *
     * @param string $name
     * @param ConnectorResponse $response
     * @return string
     * @throws \ActiveCollab\Narrative\Error\ConnectorError
     */
    function addPersonaFromResponse($name, $response)
    {
      throw new ConnectorError('Persona creation not supported');
    }
  }