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

    public function info($key = null) {
        $info = $this->client->get('/')->getData();
        return ($key && isset($info[$key]))
            ? $info[$key] : $info;
    }
    public function version() {
        return $this->info('version');
    }

    public function getActiveTasks() {
        return $this->client->get('/_active_tasks')->getData();
    }
    public function getAllDatabases() {
        return $this->client->get('/_all_dbs')->getData();
    }
}
