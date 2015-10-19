<?php
namespace Couch\Object;

class Document
    extends \Couch\Object
{
    private $data = [];

    public function __construct($client, array $data = null) {
        parent::__construct($client);

        if (!empty($data)) {
            $this->setData($data);
        }
    }

    public function setData(array $data) {
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }
}
