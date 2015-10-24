<?php
/**
 * Copyright 2015 Kerem Güneş
 *    <http://qeremy.com>
 *
 *Apache License, Version 2.0
 *    <http://www.apache.org/licenses/LICENSE-2.0>
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 */
namespace Couch;

use \Couch\Client;
use \Couch\Query;
use \Couch\Util\Util,
    \Couch\Util\Property;

/**
 * @package Couch
 * @object  Couch\Couch
 * @uses    Couch\Client
 * @uses    Couch\Query
 * @uses    Couch\Util\Util,
 *          Couch\Util\Property
 * @author  Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class Database
{
    /**
     * Property object (trait).
     * @var Couch\Util\Property
     */
    use Property;

    /**
     * Client object.
     * @var Couch\Client
     */
    private $client;

    /**
     * Database name.
     * @var string
     */
    private $name;

    /**
     * Object constructor.
     *
     * @param Couch\Client $client
     * @param string       $name
     */
    public function __construct(Client $client, $name) {
        $this->client = $client;
        $this->name = $name;
    }

    /**
     * Ping database, expect 200 response code.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/common.html#head--{db}
     * @return bool
     */
    public function ping() {
        return (200 === $this->client->head('/')->getStatusCode());
    }

    /**
     * Get database info.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/common.html#get--{db}
     * @param  string|null $key
     * @return mixed
     */
    public function info($key = null) {
        $info = $this->client->get('/'. $this->name)->getData();

        // return all info
        if ($key === null) {
            return $info;
        }

        // return specific info value
        return Util::dig($key, $info);
    }

    /**
     * Create a new database.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/common.html#put--{db}
     * @return bool
     */
    public function create() {
        return (true === $this->client->put('/'. $this->name)->getData('ok'));
    }

    /**
     * Remove database.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/common.html#delete--{db}
     * @return bool
     */
    public function remove() {
        return (true === $this->client->delete('/'. $this->name)->getData('ok'));
    }

    /**
     * Replicate database.
     *
     * @link   http://docs.couchdb.org/en/1.4.x/api/misc.html?highlight=_replicate#post-replicate
     * @param  string $target
     * @param  bool   $targetCreate
     * @return mixed
     */
    public function replicate($target, $targetCreate = true) {
        return $this->client->post('/_replicate', null, [
            'source' => $this->name, 'target' => $target, 'create_target' => $targetCreate
        ])->getData();
    }


    /**
     * Get a document by given key (docid).
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#get--{db}-_all_docs
     * @param  string $key
     * @return mixed
     */
    public function getDocument($key) {
        $data = $this->client->get('/'. $this->name .'/_all_docs', [
            'include_docs' => true,
            'key' => sprintf('"%s"', Util::quote($key))
        ])->getData();

        if (isset($data['rows'][0])) {
            return $data['rows'][0];
        }
    }

    /**
     * Get all documents by given query options. If keys params provided, request for
     * documents by given keys.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#get--{db}-_all_docs
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#post--{db}-_all_docs
     * @param  mixed  $query
     * @param  array  $keys
     * @return mixed
     */
    public function getDocumentAll($query = null, array $keys = array()) {
        if ($query instanceof Query) {
            $query = $query->toArray();
        } elseif (is_string($query)) {
            parse_str($query, $query);
        }
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

    /**
     * Create a document.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#inserting-documents-in-bulk
     * @param  mixed $document
     * @return mixed
     */
    public function createDocument($document) {
        $data = $this->createDocumentAll([$document]);
        if (isset($data[0])) {
            return $data[0];
        }
    }

    /**
     * Create multiple documents.
     *
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/database/bulk-api.html#inserting-documents-in-bulk
     * @note   Each array element could be "array", "stdClass" or "Couch\Document".
     * @param  array $documents
     * @return mixed
     */
    public function createDocumentAll(array $documents) {
        $docs = array();
        foreach ($documents as $document) {
            if ($document instanceof Document) {
                $document = $document->getData();
            } elseif ($document instanceof \stdClass) {
                $document = (array) $document;
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
        $docs = array();
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
        $docs = array();
        foreach ($documents as $document) {
            if ($document instanceof Document) {
                $document = $document->getData();
            }
            $docs[] = $document + ['_deleted' => true];
        }

        return $this->updateDocumentAll($docs);
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/changes.html#get--{db}-_changes
    public function getChanges(array $query = null, array $docIds = array()) {
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
    // http://docs.couchdb.org/en/1.5.1/api/database/misc.html#db-missing-revs
    public function getMissingRevisions($docId, array $docRevs) {
        return $this->client->post('/'. $this->name .'/_missing_revs', null,
            [$docId => $docRevs],
            ['Content-Type' => 'application/json']
        )->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/database/misc.html#db-revs-diff
    public function getMissingRevisionsDiff($docId, array $docRevs) {
        return $this->client->post('/'. $this->name .'/_revs_diff', null,
            [$docId => $docRevs],
            ['Content-Type' => 'application/json']
        )->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/database/misc.html#get--{db}-_revs_limit
    public function getRevisionLimit() {
        return $this->client->get('/'. $this->name .'/_revs_limit')->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/database/misc.html#put--{db}-_revs_limit
    public function setRevisionLimit($limit) {
        return $this->client->put('/'. $this->name .'/_revs_limit', null, $limit)->getData();
    }
}

