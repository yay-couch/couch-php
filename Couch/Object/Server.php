<?php
namespace Couch\Object;

use \Couch\Client;

class Server
{
    private $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function ping() {
        $response = $this->client->request('GET /');
        return (200 === $response->statusCode);
    }

    public function info() {
        $response = $this->client->request('GET /');
        return $response->body;
    }
}
