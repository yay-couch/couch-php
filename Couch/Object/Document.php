<?php
namespace Couch\Object;

class Document
    extends \Couch\Object
{
    private $id, $rev;
    private $data = [];

    public function __construct($client, array $data = null) {
        parent::__construct($client);

        if (!empty($data)) {
            $this->setData($data);
        }
    }

    public function setId($id) {
        $this->id = $id;
    }
    public function setRev($rev) {
        $this->rev = $rev;
    }

    public function getId($id) {
        return $this->id;
    }
    public function getRev($rev) {
        return $this->rev;
    }

    public function setData(array $data) {
        foreach ($data as $key => $value) {
            if ($key == '_id') {
                $this->setId($value);
            } elseif ($key == '_rev') {
                $this->setRev($value);
            } else {
                $this->data[$key] = $value;
            }
        }
    }

    public function getData() {
        return $this->data;
    }
}
