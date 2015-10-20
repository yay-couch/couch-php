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
    public function replicate($target) {
        return $this->client->post('/_replicate', null, [
            'source' => $this->name, 'target' => $target, 'create_target' => true
        ])->getData();
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

    // http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#inserting-documents-in-bulk
    public function createDocument($document) {
        $data = $this->createDocumentAll([$document]);
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

    // http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#updating-documents-in-bulk
    public function updateDocument($document) {
        $data = $this->updateDocumentAll([$document]);
        if (isset($data[0])) {
            return $data[0];
        }
    }
    public function updateDocumentAll(array $documents) {
        $docs = [];
        foreach ($documents as $document) {
            if ($document instanceof Document) {
                $document = $document->getData();
            }
            // these are required params
            if (!isset($document['_id'], $document['_rev'])) {
                throw new Exception('Both _id & _rev fields are required!');
            }
            $docs[] = $document;
        }

        return $this->client->post('/'. $this->name .'/_bulk_docs', null, ['docs' => $docs])->getData();
    }

    public function deleteDocument($document) {
        $data = $this->deleteDocumentAll([$document]);
        if (isset($data[0])) {
            return $data[0];
        }
    }
    public function deleteDocumentAll(array $documents) {
        $docs = [];
        foreach ($documents as $document) {
            if ($document instanceof Document) {
                $document = $document->getData();
            }
            $docs[] = $document + ['_deleted' => true];
        }

        return $this->updateDocumentAll($docs);
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/changes.html#get--{db}-_changes
    public function getChanges(array $query = null, array $docIds = []) {
        if (empty($docIds)) {
            return $this->client->get('/'. $this->name .'/_changes', $query)->getData();
        } else {
            if (!isset($query['filter'])) {
                $query['filter'] = '_doc_ids';
            }
            return $this->client->post('/'. $this->name .'/_changes', $query, ['doc_ids' => $docIds])->getData();
        }
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/compact.html#db-compact
    public function compact($designDocument = null) {
        if (empty($designDocument)) {
            return $this->client->post('/'. $this->name .'/_compact', null, null, [
                'Content-Type' => 'application/json'
            ])->getData();
        } else {
            return $this->client->post('/'. $this->name .'/_compact/'. $designDocument, null, null, [
                'Content-Type' => 'application/json'
            ])->getData();
        }
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/compact.html#db-ensure-full-commit
    public function ensureFullCommit() {
        return $this->client->post('/'. $this->name .'/_ensure_full_commit', null, null, [
            'Content-Type' => 'application/json'
        ])->getData();
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/compact.html#db-view-cleanup
    public function viewCleanup() {
        return $this->client->post('/'. $this->name .'/_view_cleanup', null, null, [
            'Content-Type' => 'application/json'
        ])->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/database/temp-views.html#db-temp-view
    public function viewTemp($map, $reduce = null) {
        return $this->client->post('/'. $this->name .'/_temp_view', null, [
            'map' => $map,
            'reduce' => $reduce
        ])->getData();
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/security.html#get--{db}-_security
    public function getSecurity() {
        return $this->client->get('/'. $this->name .'/_security')->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/database/security.html#put--{db}-_security
    public function setSecurity(array $admins, array $members) {
        if (!isset($admins['names'], $admins['roles']) ||
            !isset($members['names'], $members['roles'])) {
            throw new Exception('Specify admins and/or members with names=>roles fields!');
        }

        return $this->client->put('/'. $this->name .'/_security', null, [
            'admins' => $admins,
            'members' => $members
        ])->getData();
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/misc.html#db-purge
    public function purge($docId, array $docRevs) {
        return $this->client->post('/'. $this->name .'/_purge', null,
            [$docId => $docRevs],
            ['Content-Type' => 'application/json']
        )->getData();
    }
}

