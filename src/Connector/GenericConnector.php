<?php

namespace ActiveCollab\Narrative\Connector;

use ActiveCollab\Narrative\Error\ConnectorError;
use ActiveCollab\Narrative\ConnectorResponse;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * @package ActiveCollab\Narrative\Connector
 */
class GenericConnector extends Connector
{
    /**
     * @var string
     */
    private $base_url;

    /**
     * @var string
     */
    private $token;

    /**
     * @var Client
     */
    private $client;

    /**
     * Construct and configure the connector
     *
     * @param array $parameters
     */
    function __construct(array $parameters = null)
    {
        $this->base_url = $parameters['base_url'] ?? '';

        if (!empty($this->base_url)) {
            $this->base_url = rtrim($this->base_url, '/') . '/';
        }

        $this->token = $parameters['token'] ?? '';

        $this->client = new Client();
    }

    /**
     * Send a get request
     *
     * @param  string            $path
     * @param  string            $persona
     * @return ConnectorResponse
     * @throws ConnectorError
     */
    function get($path, $persona = Connector::DEFAULT_PERSONA)
    {
        try {
            $request = new Request('GET', $this->getAbsoluteUrl($path), ["Content-Type" => "application/json"]);

            try {
                $referece = microtime(true);
                $response = $this->client->send($request);

                return $this->clientResponseToConnectorResponse($response, microtime(true) - $referece);
            } catch (RequestException $e) {
                return $this->clientResponseToConnectorResponse($e->getResponse());
            }
        } catch (Exception $e) {
            throw new ConnectorError();
        }
    }

    /**
     * Send a POST request
     *
     * @param string     $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string     $persona
     * @return ConnectorResponse
     * @throws ConnectorError
     */
    function post($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA)
    {
        try {
            if ($params === null) {
                $body = null;
            } else {
                $body = json_encode($params);
            }

            $request = new Request('POST', $this->getAbsoluteUrl($path), ["Content-Type" => "application/json"], $body);

            try {
                $referece = microtime(true);
                $response = $this->client->send($request);

                return $this->clientResponseToConnectorResponse($response, microtime(true) - $referece);
            } catch (RequestException $e) {
                return $this->clientResponseToConnectorResponse($e->getResponse());
            }
        } catch (Exception $e) {
            throw new ConnectorError('Connector error: ' . $e->getMessage());
        }
    }

    /**
     * Send a PUT request
     *
     * @param string     $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string     $persona
     * @return ConnectorResponse
     * @throws ConnectorError
     */
    function put($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA)
    {
        try {
            if ($params === null) {
                $body = null;
            } else {
                $body = json_encode($params);
            }

            $request = new Request('PUT', $this->getAbsoluteUrl($path), ["Content-Type" => "application/json"], $body);

            try {
                $referece = microtime(true);
                $response = $this->client->send($request);

                return $this->clientResponseToConnectorResponse($response, microtime(true) - $referece);
            } catch (RequestException $e) {
                return $this->clientResponseToConnectorResponse($e->getResponse());
            }
        } catch (Exception $e) {
            throw new ConnectorError('Connector error: ' . $e->getMessage());
        }
    }

    /**
     * Send a delete command
     *
     * @param string     $path
     * @param array|null $params
     * @param string     $persona
     * @return ConnectorResponse
     * @throws ConnectorError
     */
    function delete($path, $params = null, $persona = Connector::DEFAULT_PERSONA)
    {
        try {
            $request = new Request('DELETE', $this->getAbsoluteUrl($path), ["Content-Type" => "application/json"]);

            try {
                $referece = microtime(true);
                $response = $this->client->send($request);

                return $this->clientResponseToConnectorResponse($response, microtime(true) - $referece);
            } catch (RequestException $e) {
                return $this->clientResponseToConnectorResponse($e->getResponse());
            }
        } catch (Exception $e) {
            throw new ConnectorError('Connector error: ' . $e->getMessage());
        }
    }

    /**
     * Return absolute request URL
     *
     * @param  string $path
     * @return string
     */
    private function getAbsoluteUrl($path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        } else {
            return $this->base_url . ltrim($path, '/');
        }
    }

    /**
     * Turn PSR7 response to connector response object
     *
     * @param  ResponseInterface $response
     * @param  int|float         $execution_time
     * @return ConnectorResponse
     */
    private function clientResponseToConnectorResponse(ResponseInterface $response, $execution_time = 0)
    {
        return new ConnectorResponse($response->getStatusCode(), $response->getHeaderLine('Content-Type'), $response->getHeaderLine('Content-Length'), (string) $response->getBody(), round($execution_time, 5));
    }

    /**
     * Create a new persona based on a response
     *
     * @param  string            $name
     * @param  ConnectorResponse $response
     * @return string
     * @throws ConnectorError
     */
    function addPersonaFromResponse($name, $response)
    {
        throw new ConnectorError('Persona creation not supported in generic connector');
    }
}