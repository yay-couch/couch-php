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
 * @object     Couch\Http\Stream
 * @author     Kerem Güneş <qeremy[at]gmail[dot]com>
 */
abstract class Stream
{
    /**
     * Stream types.
     * @const int
     */
    const TYPE_REQUEST = 1,
          TYPE_RESPONSE = 2;

    /**
     * Stream type that used in self.toString method.
     * @var int
     */
    protected $type;

    /**
     * Stream body.
     * @var mixed
     */
    protected $body;

    /**
     * Stream headers.
     * @var array
     */
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
            foreach ($this->headers as $key => $value) {
                // actually remove header command
                if ($value === null) {
                    continue;
                }
                $headers[$key] = $value;
            }

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
