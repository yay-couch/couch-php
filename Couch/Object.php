<?php
namespace Couch;

class Object
{
    protected $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function setClient(Client $client) {
        $this->client = $client;
    }

    public function getClient() {
        return $this->client;
    }
}
