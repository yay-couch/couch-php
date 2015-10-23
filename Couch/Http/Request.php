<?php
namespace Couch\Http;

use \Couch\Couch;
use \Couch\Client;
use \Couch\Util\Property;

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
        $this->type = parent::TYPE_REQUEST;

        $this->client = $client;

        if ($client->username && $client->password) {
            $this->headers['Authorization'] =
                'Basic '. base64_encode($client->username .':'. $client->password);
        }

        $this->headers['Accept'] = 'application/json';
        $this->headers['Content-Type'] = 'application/json';
        $this->headers['User-Agent'] = 'Couch/v'. Couch::VERSION .' (+http://github.com/qeremy/couch)';
    }

    public function send() {
        // just only debugging outbound headers in an order
        ksort($this->headers);

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
        $this->setHeader('X-HTTP-Method-Override', $this->method);

        return $this;
    }
    public function setUri($uri, array $uriParams = null) {
        if (!empty($uriParams)) {
            // convert booleans
            foreach ($uriParams as $key => $value) {
                if (is_bool($value)) {
                    $uriParams[$key] = $value ? 'true' : 'false';
                }
            }
            $uri = str_replace(['%5B', '%5D'], ['[', ']'],
                sprintf('%s?%s', $uri, http_build_query($uriParams)));
        }
        $this->uri = $uri;

        return $this;
    }

    public function getMethod() {
        return $this->method;
    }
    public function getUri() {
        return $this->uri;
    }

    public function setBody($body) {
        if (!empty($body)) {
            if ($this->headers['Content-Type'] == 'application/json') {
                $this->body = json_encode($body);
            } else {
                $this->body = $body;
            }
        }
        $this->headers['Content-Length'] = strlen($this->body);

        return $this;
    }
    public function setHeader($key, $value) {
        $this->headers[$key] = $value;

        return $this;
    }
}
