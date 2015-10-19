<?php
namespace Couch\Object;

class Database
    extends \Couch\Object
{
    private $name;

    public function __construct($client, $name) {
        parent::__construct($client);

        $this->setName($name);
    }

    public function setName($name) {
        $this->name = $name;
    }
    public function getName() {
        return $this->name;
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/common.html#head--{db}
    public function ping() {
        return (200 === $this->client->head('/')->getStatusCode());
    }
    // http://docs.couchdb.org/en/1.5.1/api/database/common.html#get--{db}
    public function info($key = null) {
        $info = $this->client->get('/'. $this->name)->getData();
        return ($key && isset($info[$key]))
            ? $info[$key] : $info;
    }
    // http://docs.couchdb.org/en/1.5.1/api/database/common.html#put--{db}
    public function create() {
        return (true === $this->client->put('/'. $this->name)->getData('ok'));
    }
    // http://docs.couchdb.org/en/1.5.1/api/database/common.html#delete--{db}
    public function remove() {
        return (true === $this->client->delete('/'. $this->name)->getData('ok'));
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#get--{db}-_all_docs
    public function getAllDocuments(array $query = null, array $keys = []) {
        if (empty($keys)) {
            return $this->client->get('/'. $this->name .'/_all_docs', $query)->getData();
        } else {
            return $this->client->post('/'. $this->name .'/_all_docs', null, ['keys' => $keys])->getData();
        }
    }
}
