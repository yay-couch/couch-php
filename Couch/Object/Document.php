<?php
namespace Couch\Object;

use Couch\Uuid;
use Couch\Util\Util;

class Document
    extends \Couch\Object
         implements \JsonSerializable
{
    private $id, $rev, $deleted = false, $attachments = [];
    private $db, $database;
    private $data = [];

    public function __construct(Database $database = null, array $data = []) {
        if ($database) {
            $this->db = $this->database = $database;

            parent::__construct($database->getClient());
        }

        if (!empty($data)) {
            $this->setData($data);
        }
    }

    public function __set($key, $value) {
        $this->setData([$key => $value]);
    }
    public function __get($key) {
        return $this->getData($key);
    }
    public function jsonSerialize() {
        return $this->data;
    }

    public function setId($id) {
        if (!$this->id instanceof Uuid) {
            $id = new Uuid($id);
        }
        $this->id = $id;
    }
    public function setRev($rev) {
        $this->rev = $rev;
    }
    public function setDeleted($deleted) {
        $this->deleted = (bool) $deleted;
    }

    public function getId() {
        return $this->id;
    }
    public function getRev() {
        return $this->rev;
    }
    public function getDeleted() {
        return $this->deleted;
    }

    public function setAttachment($attachment) {}
    public function getAttachment($name) {}
    public function getAttachmentAll() {}
    public function unsetAttachment($name) {}
    public function unsetAttachmentAll() {}

    public function setData(array $data) {
        if (isset($data['_id']))      $this->setId($data['_id']);
        if (isset($data['_rev']))     $this->setRev($data['_rev']);
        if (isset($data['_deleted'])) $this->setDeleted($data['_deleted']);
        if (isset($data['_attachments'])) {
            // unset?
            $this->setAttachment($data['_attachments']);
        }

        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function getData($key = null) {
        if ($key) {
            return Util::getArrayValue($key, $this->data);
        }
        return $this->data;
    }

    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#head--{db}-{docid}
    public function ping($statusCode = 200) {
        if (empty($this->id)) {
            throw new Exception('_id field is could not be empty!');
        }
        $headers = [];
        if ($this->rev) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->rev);
        }
        $response = $this->client->head('/'. $this->db->getName(). '/'. $this->id, null, $headers);
        return in_array($response->getStatusCode(), (array) $statusCode);
    }
    public function isExists() {
        return $this->ping([200, 304]);
    }
    public function isNotModified() {
        if (empty($this->rev)) {
            throw new Exception('_rev field could not be empty!');
        }
        return $this->ping(304);
    }

    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#get--{db}-{docid}
    public function find(array $query = null) {
        if (empty($this->id)) {
            throw new Exception('_id field could not be empty!');
        }
        return $this->client->get($this->db->getName() .'/'. $this->id, $query)->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#getting-a-list-of-revisions
    public function findRevisions() {
        $data = $this->find(['revs' => true]);
        if (isset($data['_revisions'])) {
            return $data['_revisions'];
        }
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#obtaining-an-extended-revision-history
    public function findRevisionsExtended() {
        $data = $this->find(['revs_info' => true]);
        if (isset($data['_revs_info'])) {
            return $data['_revs_info'];
        }
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#attachments
    public function findAttachments($attEncInfo = false, array $attsSince = null) {
        $query = [];
        $query['attachments'] = true;
        $query['att_encoding_info'] = $attEncInfo;
        if ($attsSince)  {
            $attsSinceArray = [];
            foreach ($attsSince as $attsSinceValue) {
                $attsSinceArray[] = sprintf('"%s"', Util::quote($attsSinceValue));
            }
            $query['atts_since'] = sprintf('[%s]', join(',', $attsSinceArray));
        }
        $data = $this->find($query);
        if (isset($data['_attachments'])) {
            return $data['_attachments'];
        }
    }

    // http://docs.couchdb.org/en/1.5.1/api/database/common.html#post--{db}
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#put--{db}-{docid} (not used)
    public function save($batch = false, $fullCommit = false) {
        $batch = $batch ? '?batch=ok' : '';
        $headers = [];
        $headers['Content-Type'] = 'application/json';
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->client->post($this->db->getName() . $batch, null, $this->getData(), $headers)->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#delete--{db}-{docid}
    public function remove($batch = false, $fullCommit = false) {
        if (empty($this->id) || empty($this->rev)) {
            throw new Exception('Both _id & _rev fields could not be empty!');
        }
        $batch = $batch ? '?batch=ok' : '';
        $headers = [];
        $headers['If-Match'] = $this->rev;
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->client->delete($this->db->getName() .'/'. $this->id . $batch, null, $headers)->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#copy--{db}-{docid}
    public function copy($destination, $batch = false, $fullCommit = false) {
        if (empty($this->id)) {
            throw new Exception('_id field could not be empty!');
        }
        if (empty($destination)) {
            throw new Exception('Destination could not be empty!');
        }
        $batch = $batch ? '?batch=ok' : '';
        $headers = [];
        $headers['Destination'] = $destination;
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->client->copy($this->db->getName() .'/'. $this->id . $batch, null, $headers)->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#copying-from-a-specific-revision
    public function copyFrom($destination, $batch = false, $fullCommit = false) {
        if (empty($this->id) || empty($this->rev)) {
            throw new Exception('Both _id & _rev fields could not be empty!');
        }
        if (empty($destination)) {
            throw new Exception('Destination could not be empty!');
        }
        $batch = $batch ? '?batch=ok' : '';
        $headers = [];
        $headers['If-Match'] = $this->rev;
        $headers['Destination'] = $destination;
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->client->copy($this->db->getName() .'/'. $this->id . $batch, null, $headers)->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#copying-to-an-existing-document
    public function copyTo($destination, $destinationRevision, $batch = false, $fullCommit = false) {
        if (empty($this->id) || empty($this->rev)) {
            throw new Exception('Both _id & _rev fields could not be empty!');
        }
        if (empty($destination)) {
            throw new Exception('Destination and destination revision could not be empty!');
        }
        $batch = $batch ? '?batch=ok' : '';
        $headers = [];
        $headers['If-Match'] = $this->rev;
        $headers['Destination'] = sprintf('%s?rev=%s', $destination, $destinationRevision);
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->client->copy($this->db->getName() .'/'. $this->id . $batch, null, $headers)->getData();
    }
}
