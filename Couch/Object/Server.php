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

    public function version() {
        return $this->info('version');
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#api-server-root
    public function info($key = null) {
        $info = $this->client->get('/')->getData();
        return ($key && isset($info[$key]))
            ? $info[$key] : $info;
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#active-tasks
    public function getActiveTasks() {
        return $this->client->get('/_active_tasks')->getData();
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#all-dbs
    public function getAllDatabases() {
        return $this->client->get('/_all_dbs')->getData();
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#db-updates
    public function getDatabaseUpdates($query = null) {
        return $this->client->get('/_db_updates', $query)->getData();
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#log
    public function getLogs($query = null) {
        return $this->client->get('/_log', $query)->getBody();
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#replicate
    public function replicate($query) {
        if (!isset($query['source'], $query['target'])) {
            throw new Exception('Both source & target required!');
        }
        return $this->client->post('/_replicate', null, $query)->getData();
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#restart
    public function restart() {
        return (202 === $this->client->post('/_restart')->getStatusCode());
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#stats
    public function getStats($path = null) {
        return $this->client->get('/_stats/'. $path)->getData();
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/common.html#uuids
    public function getUuid($count = 1) {
        $data = $this->client->get('/_uuids', ['count' => $count])->getData('uuids');
        return ($count === 1) ? $data[0] : $data;
    }

    // http://docs.couchdb.org/en/1.5.1/api/server/configuration.html
    public function getConfig($section = null, $key = null) {
        $path = join('/', array_filter([$section, $key]));
        return $this->client->get('/_config/'. $path)->getData();
    }
    public function setConfig($section, $key, $value) {
        $path = join('/', [$section, $key]);
        $response = $this->client->put('/_config/'. $path, null, $value);
        return (200 === $response->getStatusCode())
            ? $response->getData() : false;
    }
    public function removeConfig($section, $key) {
        $path = join('/', [$section, $key]);
        $response = $this->client->delete('/_config/'. $path);
        return (200 === $response->getStatusCode())
            ? $response->getData() : false;
    }
}
