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

use \Couch\Util\Property;

/**
 * @package    Couch
 * @subpackage Couch\Http
 * @object     Couch\Http\Response
 * @uses       Couch\Util\Property
 * @extends    Couch\Http\Stream
 * @author     Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class Response
    extends Stream
{
    /**
     * Property object.
     * @var Couch\Util\Property
     */
    use Property;

    /**
     * Status stuff.
     * @var int, string
     */
    private $statusCode,
            $statusText;

    /**
     * Object constructor.
     *
     * @param Couch\Http\Agent $agent
     */
    public function __construct(Agent $agent) {
        $this->type = parent::TYPE_RESPONSE;

        // @tmp
        // pre($agent->getResult());

        // split raw response
        @list($headers, $body) =
            explode("\r\n\r\n", $agent->getResult(), 2);

        // parse headers
        $headers = Agent::parseResponseHeaders($headers);
        foreach ($headers as $key => $value) {
            $this->setHeader($key, $value);
        }

        // set status stuff
        if (isset($headers['_status']['code'], $headers['_status']['text'])) {
            $this->setStatusCode($headers['_status']['code'])
                 ->setStatusText($headers['_status']['text']);
        }

        // set body
        $this->setBody($body,
            (isset($headers['Content-Type']) &&
                   $headers['Content-Type'] == 'application/json'));
    }

    /**
     * Set status code.
     *
     * @param  int $statusCode
     * @return self
     */
    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Set status text.
     *
     * @param  int $statusText
     * @return self
     */
    public function setStatusText($statusText) {
        $this->statusText = $statusText;

        return $this;
    }

    /**
     * Get status code.
     *
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Get status text.
     *
     * @return string
     */
    public function getStatusText() {
        return $this->statusText;
    }

    /**
     * Set body.
     *
     * @param  string $body
     * @param  bool   $isJson
     * @return self
     */
    public function setBody($body, $isJson = true) {
        // decode if json
        $this->body = $isJson
            ? json_decode($body, true) : $body;

        return $this;
    }

    /**
     * Set header.
     *
     * @param  string $key
     * @param  string $value
     * @return self
     */
    public function setHeader($key, $value) {
        $this->headers[$key] = $value;

        return $this;
    }
}
