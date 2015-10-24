<?php
/**
 * Copyright 2015 Kerem Güneş
 *    <http://qeremy.com>
 *
 *Apache License, Version 2.0
 *    <http://www.apache.org/licenses/LICENSE-2.0>
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 */
namespace Couch;

use \Couch\Couch;
use \Couch\Http\Request,
    \Couch\Http\Response;
use \Couch\Util\Property;

/**
 * @package Couch
 * @object  Couch\Couch
 * @uses    Couch\Couch
 * @uses    Couch\Http\Request,
 *          Couch\Http\Response
 * @uses    Couch\Util\Property
 * @author  Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class Client
{
    /**
     * Property object (trait).
     * @var Couch\Util\Property
     */
    use Property;

    /**
     * Couch object.
     * @var Couch\Couch
     */
    private $couch;

    /**
     * CouchDB host
     * @var string
     */
    private $host = 'localhost';

    /**
     * CouchDB port
     * @var int
     */
    private $port = 5984;

    /**
     * CouchDB username & password that used in connections if provided.
     * @var string, string
     */
    private $username, $password;

    /**
     * Request, Response objects.
     * @var Couch\Http\Request,
     *      Couch\Http\Response
     */
    private $request, $response;

    /**
     * Object constructor.
     *
     * @param Couch\Couch $couch
     * @param array $config
     */
    public function __construct(Couch $couch, array $config = array()) {
        $this->couch = $couch;

        // set host & port
        if (isset($config['host'])) {
            $this->host = $config['host'];
        }
        if (isset($config['port'])) {
            $this->port = $config['port'];
        }
        // set credentials
        if (isset($config['username'])) {
            $this->username = $config['username'];
        }
        if (isset($config['password'])) {
            $this->password = $config['password'];
        }
    }

    /**
     * Get Request object.
     *
     * @return Couch\Http\Request
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * Get Response object.
     *
     * @return Couch\Http\Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Make a HTTP request using Request and return Response.
     *
     * @param strin        $uri
     * @param array|null   $uriParams
     * @param array|string $body      If array than JSON data will be sent as request body.
     * @param array        $headers
     * @return Couch\Http\Response
     * @throws Couch\Exception
     */
    public function request($uri, array $uriParams = null, $body = null, array $headers = array()) {
        // match for a valid request i.e: HEAD /foo
        preg_match('~^([a-z]+)\s+(/.*)~i', $uri, $match);
        if (!isset($match[1], $match[2])) {
            throw new Exception('Usage: <REQUEST METHOD> <REQUEST URI>');
        }

        // merge host, port and uri
        $uri = sprintf('%s:%s/%s', $this->host, $this->port, trim($match[2], '/'));

        // create request object and set it's method & uri
        $this->request = (new Request($this))
            ->setMethod($match[1])
            ->setUri($uri, $uriParams);

        // add headers if provided
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $this->request->setHeader($key, $value);
            }
        }

        // add request body
        $this->request->setBody($body);

        // make request using http agent (sock or curl)
        $agent = $this->request->send();

        // assing self.request and return it
        return ($this->response = new Response($agent));
    }

    /**
     * Make a HEAD request (i.e HEAD /foo).
     * @param  string     $uri
     * @param  array|null $uriParams
     * @param  array      $headers
     * @return Couch\Http\Response
     */
    public function head($uri, array $uriParams = null, array $headers = array()) {
        return $this->request(Request::METHOD_HEAD .' /'. $uri, $uriParams, null, $headers);
    }

    /**
     * Make a GET request (i.e GET /foo).
     * @param  string     $uri
     * @param  array|null $uriParams
     * @param  array      $headers
     * @return Couch\Http\Response
     */
    public function get($uri, array $uriParams = null, array $headers = array()) {
        return $this->request(Request::METHOD_GET .' /'. $uri, $uriParams, null, $headers);
    }

    /**
     * Make a POST request (i.e POST /foo).
     * @param  string     $uri
     * @param  array|null $uriParams
     * @param  array|null $body
     * @param  array      $headers
     * @return Couch\Http\Response
     */
    public function post($uri, array $uriParams = null, $body = null, array $headers = array()) {
        return $this->request(Request::METHOD_POST .' /'. $uri, $uriParams, $body, $headers);
    }

    /**
     * Make a PUT request (i.e PUT /foo).
     * @param  string     $uri
     * @param  array|null $uriParams
     * @param  array|null $body
     * @param  array      $headers
     * @return Couch\Http\Response
     */
    public function put($uri, array $uriParams = null, $body = null, array $headers = array()) {
        return $this->request(Request::METHOD_PUT .' /'. $uri, $uriParams, $body, $headers);
    }

    /**
     * Make a COPY request (i.e COPY /foo).
     * @param  string     $uri
     * @param  array|null $uriParams
     * @param  array      $headers
     * @return Couch\Http\Response
     */
    public function copy($uri, array $uriParams = null, array $headers = array()) {
        return $this->request(Request::METHOD_COPY .' /'. $uri, $uriParams, null, $headers);
    }

    /**
     * Make a DELETE request (i.e DELETE /foo).
     * @param  string     $uri
     * @param  array|null $uriParams
     * @param  array      $headers
     * @return Couch\Http\Response
     */
    public function delete($uri, array $uriParams = null, array $headers = array()) {
        return $this->request(Request::METHOD_DELETE .' /'. $uri, $uriParams, null, $headers);
    }
}
