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
