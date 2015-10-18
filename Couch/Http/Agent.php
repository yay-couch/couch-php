<?php
namespace Couch\Http;

abstract class Agent
{
    protected $link;

    protected $failCode = 0,
              $failText = '';

    protected $config = [
        'timeout' => 5,
        'blocking' => true
    ];

    public function __construct(array $config = []) {
        $this->config = array_merge($this->config, $config);
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
}
