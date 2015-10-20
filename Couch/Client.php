<?php
namespace Couch;

use \Couch\Couch;
use \Couch\Http\Request,
    \Couch\Http\Response;
use \Couch\Util\PropertyTrait as Property;

class Client
{
    use Property;

    private $couch;

    private $host = 'localhost';
    private $port = 5984;
    private $username,
            $password;

    private $request,
            $response;

    public function __construct(Couch $couch, array $config = []) {
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

    public function getRequest() {
        return $this->request;
    }
    public function getResponse() {
        return $this->response;
    }

    public function request($uri, array $uriParams = null, $body = null, array $headers = []) {
        preg_match('~^([a-z]+)\s+(/.*)~i', $uri, $match);
        if (!isset($match[1], $match[2])) {
            throw new Exception('Usage: <REQUEST METHOD> <REQUEST URI>');
        }

        $uri = sprintf('%s:%s/%s', $this->host, $this->port, trim($match[2], '/'));

        $this->request = (new Request($this))
            ->setMethod($match[1])
            ->setUri($uri, $uriParams)
            ->setBody($body);

        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $this->request->setHeader($key, $value);
            }
        }

        $agent = $this->request->send();

        return ($this->response = new Response($agent));
    }

    public function head($uri, array $uriParams = null, array $headers = []) {
        return $this->request(Request::METHOD_HEAD .'/'. $uri, $uriParams, null, $headers);
    }

    public function get($uri, array $uriParams = null, array $headers = []) {
        return $this->request(Request::METHOD_GET .'/'. $uri, $uriParams, null, $headers);
    }

    public function post($uri, array $uriParams = null, $body = null, array $headers = []) {
        return $this->request(Request::METHOD_POST .'/'. $uri, $uriParams, $body, $headers);
    }

    public function put($uri, array $uriParams = null, $body = null, array $headers = []) {
        return $this->request(Request::METHOD_PUT .'/'. $uri, $uriParams, $body, $headers);
    }

    public function copy($uri, array $uriParams = null, $body = null, array $headers = []) {
        return $this->request(Request::METHOD_COPY .'/'. $uri, $uriParams, null, $headers);
    }

    public function delete($uri, array $uriParams = null, $body = null, array $headers = []) {
        return $this->request(Request::METHOD_DELETE .'/'. $uri, $uriParams, null, $headers);
    }
}
