<?php
namespace Couch\Http;

abstract class Stream
{
    const TYPE_REQUEST = 1,
          TYPE_RESPONSE = 2;

    protected $type;
    protected $body;
    protected $headers = array();

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
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
    }

    public function getHeaderAll() {
        return $this->headers;
    }

    public function toString() {
        $string = '';
        if ($this->type == self::TYPE_REQUEST) {
            $url = parse_url($this->uri);
            if (isset($url['query'])) {
                $url['query'] = '?'. $url['query'];
            } else {
                $url['query'] = '';
            }

            $headers = array();
            $headers['Host'] = $url['host'] .':'. $url['port'];
            $headers['Connection'] = 'close';
            $headers = $headers + $this->headers;

            $string = sprintf("%s %s%s HTTP/1.0\r\n", $this->method, $url['path'], $url['query']);
            foreach ($headers as $key => $value) {
                $string .= sprintf("%s: %s\r\n", $key, $value);
            }
            $string .= "\r\n";
            $string .= $this->body;
        } elseif ($this->type == self::TYPE_RESPONSE) {
            $string = sprintf("HTTP/1.0 %s %s\r\n", $this->statusCode, $this->statusText);
            foreach ($this->headers as $key => $value) {
                if ($key == '_status') {
                    continue;
                }
                $string .= sprintf("%s: %s\r\n", $key, $value);
            }

            if (is_array($this->body)) {
                $string .= "\r\n". json_encode($this->body);
            } else {
                $string .= "\r\n". $this->body;
            }
        }

        return $string;
    }

    abstract public function setBody($body);
    abstract public function setHeader($key, $value);
}
