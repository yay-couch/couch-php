<?php
namespace Couch\Http;

abstract class Agent
{
    protected $link;
    protected $result;

    protected $failCode = 0,
              $failText = '';

    protected $config = [
        'timeout' => 5,
        'blocking' => 1
    ];

    public function __construct(array $config = []) {
        $this->config = array_merge($this->config, $config);
    }

    public function getResult() {
        return $this->result;
    }

    public function isFail() {
        return ('' !== $this->failText);
    }
    public function getFailCode() {
        return $this->failCode;
    }
    public function getFailText() {
        return $this->failText;
    }

    public static function parseRequestHeaders($headers) {}

    public static function parseResponseHeaders($headers) {
        $headers =@ explode("\r\n", trim($headers));
        preg_match('~^HTTP/\d\.\d (\d+) ([\w- ]+)~i', array_shift($headers), $match);

        $statusCode = (int) $match[1];
        $statusText = $match[2];

        $return = [];
        $return['_status']['code'] = $statusCode;
        $return['_status']['text'] = $statusText;

        foreach ($headers as $header) {
            @list($key, $value) = explode(':', trim($header), 2);
            if (!$key) {
                continue;
            }
            $value = trim($value);
            // handle multi-headers as array
            if (isset($return[$key])) {
                $return[$key] = array_merge((array) $return[$key], [$value]);
                continue;
            }
            $return[$key] = $value;
        }

        return $return;
    }

    abstract public function run(Request $request);
    abstract public function clean();
}
