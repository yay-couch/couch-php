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
        return (200 === $this->client->head('/')->getStatusCode());
    }

    public function info() {
        return $this->client->get('/')->getData();
    }
}
