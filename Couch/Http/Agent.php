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

/**
 * @package    Couch
 * @subpackage Couch\Http
 * @object     Couch\Http\Agent
 * @author     Kerem Güneş <qeremy[at]gmail[dot]com>
 */
abstract class Agent
{
    /**
     * Resource object.
     * @var resource
     */
    protected $link;

    /**
     * Result data.
     * @var string
     */
    protected $result;

    /**
     * Fail stuff.
     * @var int, string
     */
    protected $failCode = 0,
              $failText = '';

    /**
     * Config array.
     * @var array
     */
    protected $config = array(
        'timeout'  => 5,
        'blocking' => 1
    );

    /**
     * Object constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = array()) {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get result data.
     * @return string
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * Check if fail.
     *
     * @return bool
     */
    public function isFail() {
        return ('' !== $this->failText);
    }

    /**
     * Get fial code.
     *
     * @return int
     */
    public function getFailCode() {
        return $this->failCode;
    }

    /**
     * Get fail text.
     *
     * @return string
     */
    public function getFailText() {
        return $this->failText;
    }

    // @wait
    public static function parseRequestHeaders($headers) {}

    /**
     * Parse response headers.
     *
     * @param  string $headers
     * @return array
     */
    public static function parseResponseHeaders($headers) {
        $headers =@ explode("\r\n", trim($headers));

        // extract status line
        preg_match('~^HTTP/\d\.\d (\d+) ([\w- ]+)~i', array_shift($headers), $match);

        // set status stuff
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
                $return[$key] = array_merge((array) $return[$key], array($value));
                continue;
            }

            $return[$key] = $value;
        }

        return $return;
    }

    /**
     * Run a request using agent object.
     *
     * @param  Couch\Http\Request $request
     * @return bool
     * @throws Couch\Http\Exception
     */
    abstract public function run(Request $request);

    /**
     * Clean resource.
     *
     * @return void
     */
    abstract public function clean();
}
