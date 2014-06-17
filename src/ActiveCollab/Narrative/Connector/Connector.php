<?php

  namespace ActiveCollab\Narrative\Connector;

  use ActiveCollab\Narrative\Error\ConnectorError;

  /**
   * Basic connector
   *
   * @package ActiveCollab\Narrative\Connector
   */
  abstract class Connector {

    const DEFAULT_PERSONA = 'default';

    /**
     * Send a get request
     *
     * @param string $path
     * @param string|null $persona
     */
    abstract function get($path, $persona = Connector::DEFAULT_PERSONA);

    /**
     * Send a POST request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string|null $persona
     */
    abstract function post($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA);

    /**
     * Send a PUT request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string|null $persona
     */
    abstract function put($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA);

    /**
     * Send a delete command
     *
     * @param string $path
     * @param array|null $params
     * @param string|null $persona
     */
    abstract function delete($path, $params = null, $persona = Connector::DEFAULT_PERSONA);

    /**
     * @var array
     */
    private $personas = [];

    /**
     * Return a specific persona settings
     *
     * @param string $name
     * @return array
     * @throws \ActiveCollab\Narrative\Error\ConnectorError
     */
    function getPersona($name = Connector::DEFAULT_PERSONA) {
      if(isset($this->personas[$name])) {
        return $this->personas[$name];
      } else {
        throw new ConnectorError("Persona '$name' not found");
      }
    }

    /**
     * @param string $name
     * @param array $params
     */
    function addPersona($name, array $params = []) {
      $this->personas[$name] = (array) $params;
    }

  }