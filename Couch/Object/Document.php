<?php
namespace Couch\Object;

use Couch\Uuid;
use Couch\Util\Util;

class Document
    extends \Couch\Object
{
    private $id, $rev, $deleted = false;
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

    public function setData(array $data) {
        if (isset($data['_id']))      $this->setId($data['_id']);
        if (isset($data['_rev']))     $this->setRev($data['_rev']);
        if (isset($data['_deleted'])) $this->setDeleted($data['_deleted']);

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
            return false;
        }
        $headers = [];
        if ($this->rev) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->rev);
        }
        $response = $this->client->head('/'. $this->db->getName(). '/'. $this->id, null, $headers);
        return in_array($response->getStatusCode(), (array) $statusCode);
    }
    public function isExists() {
        $this->checkId();
        return $this->ping([200, 304]);
    }
    public function isNotModified() {
        $this->checkId();
        $this->checkRev();
        return $this->ping(304);
    }

    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#get--{db}-{docid}
    public function get(array $query = null) {
        $this->checkId();
        return $this->client->get($this->db->getName() .'/'. $this->id, $query)->getData();
    }


    public function copy() {}
    public function copyFrom($destination) {
        // from: this doc
        // To copy from a specific version, use the rev argument to the query string or If-Match:
    }
    public function copyTo($destination) {
        // from: this doc
        // To copy to an existing document, you must specify the current revision string for the target document by appending the rev parameter to the Destination header string.
    }



    private function checkId() {
        if (!isset($this->id)) {
            throw new Exception('_id field is required!');
        }
    }
    private function checkRev() {
        if (!isset($this->rev)) {
            throw new Exception('_rev field is required!');
        }
    }
}
