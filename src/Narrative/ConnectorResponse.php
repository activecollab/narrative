<?php
namespace ActiveCollab\Narrative;

/**
 * @package ActiveCollab\Narrative
 */
class ConnectorResponse
{
    /**
     * @var integer
     */
    private $http_code;

    /**
     * @var string
     */
    private $content_type;

    /**
     * @var integer
     */
    private $content_length;

    /**
     * @var string|null
     */
    private $raw_response;

    /**
     * @var float
     */
    private $total_time;

    /**
     * @var array
     */
    private $info;

    /**
     * Construct the new response object
     *
     * @param integer      $http_code
     * @param string       $content_type
     * @param integer      $content_length
     * @param string|mixed $raw_response
     * @param float        $total_time
     * @param array|null   $info
     */
    public function __construct($http_code, $content_type, $content_length, $raw_response, $total_time, $info = null)
    {
        $this->http_code = $http_code;
        $this->content_type = $content_type;
        $this->content_length = $content_length;
        $this->raw_response = $raw_response;
        $this->total_time = $total_time;
        $this->info = $info;
    }

    /**
     * Return HTTP code
     *
     * @return integer
     */
    function getHttpCode()
    {
        return $this->http_code;
    }

    /**
     * Return content type
     *
     * @return string
     */
    function getContentType()
    {
        return $this->content_type;
    }

    /**
     * Return content length
     *
     * @return int
     */
    function getContentLength()
    {
        return isset($this->info['download_content_length']) && $this->info['download_content_length'] ? (integer)$this->info['download_content_length'] : 0;
    }

    /**
     * Return raw response body
     *
     * @return string
     */
    function getBody()
    {
        return $this->raw_response;
    }

    /**
     * Cached JSON data
     *
     * @var mixed
     */
    private $is_json = null, $json_loaded = false, $json = null;

    /**
     * Return true if response is JSON
     *
     * @return boolean
     */
    function isJson()
    {
        if ($this->is_json === null) {
            $this->is_json = strpos($this->getContentType(), 'application/json') !== false;
        }

        return $this->is_json;
    }

    /**
     * Return response body as JSON (when applicable)
     *
     * @return array
     */
    function getJson()
    {
        if (empty($this->json_loaded)) {
            if ($this->getBody() && $this->isJson()) {
                $this->json = json_decode($this->getBody(), true);
            }

            $this->json_loaded = true;
        }

        return $this->json;
    }

    /**
     * @return float
     */
    function getTotalTime()
    {
        return $this->total_time;
    }

    /**
     * Make all info elements available via getElementName() magic methods
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        if (substr($name, 0, 3) == 'get') {
            $bit = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', substr($name, 3)));

            if (isset($this->info[ $bit ]) && $this->info[ $bit ]) {
                return $this->info[ $bit ];
            }
        }

        return null;
    }
}
