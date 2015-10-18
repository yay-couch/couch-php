<?php
namespace Couch\Object;

use \Couch\Client;

class Server
{
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function info() {
        $response = $this->client->request('GET /');
        return $response->body;
    }

    public function ping() {
        $response = $this->client->request('GET /');
        return isset($response->headers['_status']['code'])
            && $response->headers['_status']['code'] == 200;
    }
}
