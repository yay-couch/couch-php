<?php
namespace Couch\Http;

use \Couch\Util\PropertyTrait as Property;

class Response
    extends Stream
{
    use Property;

    private $statusCode,
            $statusText;

    public function __construct(Agent $agent) {
        // pre($agent->getResult(),1);

        @list($headers, $body) =
            explode("\r\n\r\n", $agent->getResult(), 2);

        $headers = Agent::parseResponseHeaders($headers);
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        if (isset($headers['_status']['code'], $headers['_status']['text'])) {
            $this->setStatusCode($headers['_status']['code'])
                 ->setStatusText($headers['_status']['text']);
        }

        $this->setBody($body,
            (isset($headers['Content-Type']) &&
                   $headers['Content-Type'] == 'application/json'));
    }

    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }
    public function setStatusText($statusText) {
        $this->statusText = $statusText;
        return $this;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }
    public function getStatusText() {
        return $this->statusText;
    }

    public function setBody($body, $isJson = true) {
        $this->body = $isJson
            ? json_decode($body, true) : $body;
        return $this;
    }
    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }
}
