<?php
namespace Couch;

use \Couch\Couch;
use \Couch\Http\Request,
    \Couch\Http\Response;
use \Couch\Object\Server,
    \Couch\Object\Database,
    \Couch\Object\Document;
use \Couch\Util\PropertyTrait as Property;

class Client
{
    use Property;

    private $couch;

    private $host = 'localhost';
    private $port = 5984;
    private $username,
            $password;

    // private $request,
    //         $response;

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

    public function request($uri, array $uriParams = null, $body = null, array $headers = []) {
        preg_match('~^([a-z]+)\s+(/.*)~i', $uri, $match);
        if (!isset($match[1], $match[2])) {
            throw new \Exception('Usage: <REQUEST METHOD> <REQUEST URI>');
        }

        $uri = sprintf('%s:%s/%s', $this->host, $this->port, trim($match[2], '/'));
        if (!empty($uriParams)) {
            $uri = sprintf('%s?%s', $uri, http_build_query($uriParams));
        }

        $request = new Request($this);
        $request->setMethod($match[1]);
        $request->setUri($uri);
        $request->setBody($body);
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $request->setHeader($key, $value);
            }
        }

        $response = $request->send();
        return $response;
    }

    public function serverObject() {
        return new Server($this);
    }
}
