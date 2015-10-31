<?php
/**
 * Copyright 2015 Kerem Güneş
 *     <http://qeremy.com>
 *
 * Apache License, Version 2.0
 *     <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Couch\Http;

abstract class Agent
{
    protected $link;
    protected $result;

    protected $failCode = 0,
              $failText = '';

    protected $config = array(
        'timeout' => 5,
        'blocking' => 1
    );

    public function __construct(array $config = array()) {
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

        $statusCode = 0;
        $statusText = '';
        if (isset($match[1], $match[2])) {
            $statusCode = (int) $match[1];
            $statusText = $match[2];
        }

        $return = array();
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
