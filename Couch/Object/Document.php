<?php
namespace Couch\Object;

use Couch\Client;
use Couch\Util\Util;

class Document
    extends \Couch\Object
{
    private $id, $rev, $deleted = false;
    private $database;
    private $data = [];

    public function __construct(Client $client = null, Database $database = null, array $data = []) {
        if ($client) {
            parent::__construct($client);
        }

        if ($database) {
            $this->database = $database;
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
        $this->id = trim($id);
    }
    public function setRev($rev) {
        $this->rev = trim($rev);
    }
    public function setDeleted($deleted) {
        $this->deleted = (bool) $deleted;
    }

    public function getId($id) {
        return $this->id;
    }
    public function getRev($rev) {
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
}
