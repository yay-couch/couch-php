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
    }
    public function setStatusText($statusText) {
        $this->statusText = $statusText;
    }

    public function setBody($body) {
        $body = json_decode($body, true);
        $this->body = $body;
    }
    public function setBodyRaw($bodyRaw) {
        $this->bodyRaw = $bodyRaw;
    }
    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
    }
}
