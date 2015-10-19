<?php
namespace Couch\Http;

abstract class Stream
{
    protected $body;
    protected $headers = [];

    public function getData($key = null) {
        if ($key === null) {
            return $this->body;
        }

        $value =& $this->body;
        foreach (explode('.', $key) as $key) {
            $value =& $value[$key];
        }

        return $value;
    }

    public function getBody() {
        return $this->body;
    }

    public function getHeader($key) {
        return isset($this->headers[$key])
            ? $this->headers[$key] : null;
    }

    public function getHeaderAll() {
        return $this->headers;
    }

    abstract public function setBody($body);
    abstract public function setHeader($key, $value);
}
