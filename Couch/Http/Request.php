<?php
namespace Couch\Http;

use \Couch\Couch;
use \Couch\Client;
use \Couch\Util\PropertyTrait as Property;

class Request
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

    private $body,
            $bodyRaw;
    private $headers = [];

    public function __construct(Client $client) {
        $this->client = $client;

        $this->headers['Accept'] = 'application/json';
        $this->headers['User-Agent'] = 'Couch/v'. Couch::VERSION .' (+http://github.com/qeremy/couch)';
    }

    public function send() {
        $agent = $this->client->couch->getHttpAgent();
        if (false === $agent->run($this)) {
            throw new \Exception('Error!');
        }
        return new Response($agent);
    }

    public function setMethod($method) {
        $this->method = strtoupper($method);
        return $this;
    }
    public function setUri($uri) {
        $this->uri = $uri;
        return $this;
    }

    public function setBody($body) {
        $this->body = $body;
        return $this;
    }
    public function setBodyRaw($bodyRaw) {
        $this->bodyRaw = $bodyRaw;
        return $this;
    }
    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }
}
