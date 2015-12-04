<?php

  namespace ActiveCollab\Narrative\Connector;

  use ActiveCollab\Narrative\Error\ConnectorError;

  /**
   * Basic connector
   *
   * @package ActiveCollab\Narrative\Narrative\Connector
   */
  abstract class Connector
  {
    const DEFAULT_PERSONA = 'default';

    /**
     * Send a get request
     *
     * @param string $path
     * @param string|null $persona
     */
    abstract public function get($path, $persona = Connector::DEFAULT_PERSONA);

    /**
     * Send a POST request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string|null $persona
     */
    abstract public function post($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA);

    /**
     * Send a PUT request
     *
     * @param string $path
     * @param array|null $params
     * @param array|null $attachments
     * @param string|null $persona
     */
    abstract public function put($path, $params = null, $attachments = null, $persona = Connector::DEFAULT_PERSONA);

    /**
     * Send a delete command
     *
     * @param string $path
     * @param array|null $params
     * @param string|null $persona
     */
    abstract public function delete($path, $params = null, $persona = Connector::DEFAULT_PERSONA);

    /**
     * @var array
     */
    private $personas = [];

    /**
     * Return personas
     *
     * @return array
     */
    public function getPersonas()
    {
      return $this->personas;
    }

    /**
     * Return a specific persona settings
     *
     * @param string $name
     * @return array
     * @throws \ActiveCollab\Narrative\Error\ConnectorError
     */
    public function getPersona($name = Connector::DEFAULT_PERSONA) {
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
    public function addPersona($name, array $params = []) {
      $this->personas[$name] = (array) $params;
    }

    /**
     * Create a new persona based on a response
     *
     * @param string $name
     * @param mixed $response
     */
    abstract public function addPersonaFromResponse($name, $response);

    /**
     * Forget non-default personas
     */
    public function forgetNonDefaultPersonas()
    {
      foreach ($this->personas as $k => $v) {
        if ($k !== self::DEFAULT_PERSONA) {
          unset($this->personas[$k]);
        }
      }
    }
  }