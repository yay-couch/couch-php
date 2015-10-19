<?php
namespace Couch\Object;

use Couch\Client;
use Couch\Util\Util;

class Database
    extends \Couch\Object
{
    private $name;

    public function __construct(Client $client, $name) {
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
    public function getDocument($key) {
        $data = $this->client->get('/'. $this->name .'/_all_docs', [
            'include_docs' => true,
            'key' => sprintf('"%s"', Util::quote($key))
        ])->getData();

        if (isset($data['rows'][0])) {
            return $data['rows'][0];
        }
    }
    public function getDocumentAll(array $query = null, array $keys = []) {
        // always get docs
        if (!isset($query['include_docs'])) {
            $query['include_docs'] = true;
        }

        if (empty($keys)) {
            return $this->client->get('/'. $this->name .'/_all_docs', $query)->getData();
        } else {
            return $this->client->post('/'. $this->name .'/_all_docs', null, ['keys' => $keys])->getData();
        }
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#db-bulk-docs
    public function createDocument($document) {
        if ($document instanceof Document) {
            $document = $document->getData();
        }
        // this is create method, no update allowed
        if (isset($document['_id']))      unset($document['_id']);
        if (isset($document['_rev']))     unset($document['_rev']);
        if (isset($document['_deleted'])) unset($document['_deleted']);

        $data = $this->client->post('/'. $this->name .'/_bulk_docs', null, ['docs' => [$document]])->getData();
        if (isset($data[0])) {
            return $data[0];
        }
    }
    public function createDocumentAll(array $documents) {
        $docs = [];
        foreach ($documents as $document) {
            if ($document instanceof Document) {
                $document = $document->getData();
            }
            // this is create method, no update allowed
            if (isset($document['_id']))      unset($document['_id']);
            if (isset($document['_rev']))     unset($document['_rev']);
            if (isset($document['_deleted'])) unset($document['_deleted']);

            $docs[] = $document;
        }

        return $this->client->post('/'. $this->name .'/_bulk_docs', null, ['docs' => $docs])->getData();
    }
}
