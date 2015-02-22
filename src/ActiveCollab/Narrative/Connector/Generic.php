<?php

  namespace ActiveCollab\Narrative\Connector;

  use ActiveCollab\Narrative\Error\ConnectorError, ActiveCollab\Narrative\ConnectorResponse, Guzzle\Http\Client, \Exception;
  use Guzzle\Http\Message\Response;

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

    /**
     * Construct and configure the connector
     *
     * @param array $parameters
     * @throws ConnectorError
     */
    function __construct(array $parameters = null)
    {
      $this->client = new Client($parameters['url'] && $parameters['url'] ? $parameters['url'] : 'http://localhost:8000/index.php');
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
        return $this->clientResponseToConnectorResponse($this->client->get($path)->send());
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
     * @param  Response          $response
     * @return ConnectorResponse
     */
    private function clientResponseToConnectorResponse(Response $response)
    {
      if ($response instanceof Response) {
        return new ConnectorResponse($response->getStatusCode(), $response->getHeader('content-type'), $response->getHeader('content-length'), $response->getBody(), $response->getInfo('total_time'));
      }

      return $_REQUEST;
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