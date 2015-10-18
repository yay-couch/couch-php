<?php
namespace Couch\Http;

use \Couch\Util\PropertyTrait as Property;

class Response
{
    use Property;

    private $statusCode,
            $statusText;

    private $body,
            $bodyRaw;
    private $headers = [];

    public function __construct($result) {
        @list($headers, $body) = explode("\r\n\r\n", $result, 2);

        $this->setBody($body)
             ->setBodyRaw($body);

        $headers = Agent::parseResponseHeaders($headers);
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        if (isset($headers['_status']['code'], $headers['_status']['text'])) {
            $this->setStatusCode($headers['_status']['code'])
                 ->setStatusText($headers['_status']['text']);
        }
    }

    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }
    public function setStatusText($statusText) {
        $this->statusText = $statusText;
        return $this;
    }

    public function setBody($body) {
        $body = json_decode($body, true);
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
