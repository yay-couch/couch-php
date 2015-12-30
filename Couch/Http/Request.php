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

use \Couch\Couch;
use \Couch\Client;
use \Couch\Util\Property;

/**
 * @package    Couch
 * @subpackage Couch\Http
 * @object     Couch\Http\Request
 * @uses       Couch\Couch
 * @uses       Couch\Client
 * @uses       Couch\Util\Property
 * @extends    Couch\Http\Stream
 * @author     Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class Request
    extends Stream
{
    /**
     * Property object.
     * @var Couch\Util\Property
     */
    use Property;

    /**
     * Request methods.
     * @const string
     */
    const METHOD_HEAD   = 'HEAD',
          METHOD_GET    = 'GET',
          METHOD_POST   = 'POST',
          METHOD_PUT    = 'PUT',
          METHOD_COPY   = 'COPY',
          METHOD_DELETE = 'DELETE';

    /**
     * Client object.
     * @var Couch\Client
     */
    private $client;

    /**
     * Request method.
     * @var string
     */
    private $method;

    /**
     * Request URI.
     * @var string
     */
    private $uri;

    /**
     * Object constructor.
     *
     * @param Couch\Client $client
     */
    public function __construct(Client $client) {
        $this->type = parent::TYPE_REQUEST;

        // set client
        $this->client = $client;

        // prepare authorization header
        if ($client->username && $client->password) {
            $this->headers['Authorization'] =
                'Basic '. base64_encode($client->username .':'. $client->password);
        }

        // add default headers
        $this->headers['Accept'] = 'application/json';
        $this->headers['Content-Type'] = 'application/json';
        $this->headers['User-Agent'] = 'Couch/v'. Couch::VERSION .' (+http://github.com/yay-couch/couch-php)';
    }

    /**
     * Send request.
     *
     * @return Couch\Http\Agent
     * @throws Couch\Http\Exception
     */
    public function send() {
        // just only debugging outbound headers in an order
        ksort($this->headers);

        $agent = $this->client->couch->getHttpAgent();
        $agent->run($this);

        // request failed?
        if ($agent->isFail()) {
            throw new Exception(
                $agent->getFailText(), $agent->getFailCode());
        }

        return $agent;
    }

    /**
     * Set request method.
     *
     * @param string $method
     */
    public function setMethod($method) {
        $this->method = strtoupper($method);
        if ($this->method != self::METHOD_HEAD &&
            $this->method != self::METHOD_GET &&
            $this->method != self::METHOD_POST) {
            $this->setHeader('X-HTTP-Method-Override', $this->method);
        }

        return $this;
    }

    /**
     * Set request URI.
     *
     * @param  string     $uri
     * @param  array|null $uriParams
     * @return self
     */
    public function setUri($uri, array $uriParams = null) {
        if (!empty($uriParams)) {
            // convert booleans
            foreach ($uriParams as $key => $value) {
                if (is_bool($value)) {
                    $uriParams[$key] = $value ? 'true' : 'false';
                }
            }

            // build query & decode brackets
            $uri = str_replace(['%5B', '%5D'], ['[', ']'],
                sprintf('%s?%s', $uri, http_build_query($uriParams)));
        }
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get request method.
     *
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Get request URI.
     *
     * @return string
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Set request body.
     *
     * @param  mixed $body
     * @return self
     */
    public function setBody($body) {
        if (!empty($body)) {
            // content is json?
            if ($this->headers['Content-Type'] == 'application/json') {
                $this->body = json_encode($body);
            } else {
                $this->body = $body;
            }
            // set content length
            $this->headers['Content-Length'] = strlen($this->body);
        }

        return $this;
    }
}
