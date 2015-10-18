<?php
namespace Couch\Http;

use \Couch\Couch;
use \Couch\Client;
use \Couch\Util\PropertyTrait as Property;

class Request
    extends Stream
{
    use Property;

    const METHOD_HEAD   = 'HEAD',
          METHOD_GET    = 'GET',
          METHOD_POST   = 'POST',
          METHOD_PUT    = 'PUT',
          METHOD_COPY   = 'COPY',
          METHOD_DELETE = 'DELETE';

    private $client;

    private $method;
    private $uri;

    public function __construct(Client $client) {
        $this->client = $client;

        if ($client->username && $client->password) {
            $this->headers['Authorization'] =
                'Basic '. base64_encode($client->username .':'. $client->password);
        }

        $this->headers['Accept'] = 'application/json';
        $this->headers['User-Agent'] = 'Couch/v'. Couch::VERSION .' (+http://github.com/qeremy/couch)';
    }

    public function send() {
        $agent = $this->client->couch->getHttpAgent();
        $agent->run($this);

        // request failed?
        if ($agent->isFail()) {
            throw new Exception(
                $agent->getFailText(), $agent->getFailCode());
        }

        return $agent;
    }

    public function setMethod($method) {
        $this->method = strtoupper($method);

        return $this;
    }
    public function setUri($uri, array $uriParams = null) {
        $this->uri = $uri;
        if (!empty($uriParams)) {
            $this->uri = sprintf('%s?%s', $this->uri, http_build_query($uriParams));
        }

        return $this;
    }

    public function getMethod() {
        return $this->method;
    }
    public function getUri() {
        return $this->uri;
    }

    public function setBody($body, $isJson = true) {
        if (is_array($body) || is_object($body)) {
            $body = json_encode($body);
        }

        if (!empty($body)) {
            $this->setHeader('Content-Length', strlen($body));
            if ($isJson) {
                $this->setHeader('Content-Type', 'application/json');
            }
        }

        $this->body = $body;

        return $this;
    }
    public function setHeader($key, $value) {
        $this->headers[$key] = $value;

        return $this;
    }
}
