<?php
namespace Couch\Object;

class DocumentAttachment
{
    private $document;
    private $file, $fileName;
    private $data, $dataLength;
    private $contentType;
    private $digest;

    public function __construct(Document $document = null, $file = null, $fileName = null) {
        if ($document) {
            $this->document = $document;
        }
        if (!empty($file)) {
            $this->file = $file;
            if (!empty($fileName)) {
                $this->fileName = $fileName;
            } else {
                $this->fileName = basename($file);
            }
        }
    }
    public function __set($name, $value) {
        if (!property_exists($this, $name)) {
            throw new \Exception(sprintf(
                '`%s` property does not exists on this object!', $name));
        }
        $this->{$name} = $value;
    }
    public function __get($name) {
        if (!property_exists($this, $name)) {
            throw new \Exception(sprintf(
                '`%s` property does not exists on this object!', $name));
        }
        return $this->{$name};
    }

    public function setDocument(Document $document) {
        $this->document = $document;
    }
    public function getDocument() {
        return $this->document;
    }

    public function ping($statusCode = 200) {
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }
        $docId = $this->document->getId();
        $docRev = $this->document->getRev();
        if (empty($docId)) {
            throw new Exception('Attachment document _id is not defined!');
        }
        if (empty($this->fileName)) {
            throw new Exception('Attachment file name is not defined!');
        }
        $query = $headers = [];
        if (!empty($docRev)) {
            $query['rev'] = $docRev;
            // cancel using rev in headers @see https://issues.apache.org/jira/browse/COUCHDB-2860
            // $headers['If-Match'] = $docRev;
        }
        if (!empty($this->digest)) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->digest);
        }
        $response = $this->document->getClient()->head(sprintf('%s/%s/%s',
            $this->document->getDatabase()->getName(), $docId, $this->fileName), $query, $headers);
        return in_array($response->getStatusCode(), (array) $statusCode);
    }
    public function find() {
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }
        $docId = $this->document->getId();
        $docRev = $this->document->getRev();
        if (empty($docId)) {
            throw new Exception('Attachment document _id is not defined!');
        }
        if (empty($this->fileName)) {
            throw new Exception('Attachment file name is not defined!');
        }
        $query = $headers = [];
        if (!empty($docRev)) {
            $query['rev'] = $docRev;
            // cancel using rev in headers @see https://issues.apache.org/jira/browse/COUCHDB-2860
            // $headers['If-Match'] = $docRev;
        }
        if (!empty($this->digest)) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->digest);
        }
        $response = $this->document->getClient()->get(sprintf('%s/%s/%s',
            $this->document->getDatabase()->getName(), $docId, $this->fileName), $query, $headers);
        if (in_array($response->getStatusCode(), [200, 304])) {
            $return = [];
            $return['content_type'] = $response->getHeader('Content-Type');
            $return['content_length'] = $response->getHeader('Content-Length');
            $return['digest'] = trim($response->getHeader('ETag'), '"');
            $return['data'] = $response->getData();
            return $return;
        }
    }

    public function toArray($encode = true) {
        $this->readFile($encode);
        $array = [];
        $array['data'] = $this->data;
        $array['content_type'] = $this->contentType;
        return $array;
    }

    public function toJson() {
        return json_encode($this->toArray());
    }

    public function readFile($encode = true) {
        $type = finfo_file(($info = finfo_open(FILEINFO_MIME_TYPE)), $this->file);
        finfo_close($info);
        if (!$type) {
            throw new Exception("Could not open file `{$this->file}`!");
        }
        $this->contentType = $type;

        $data = file_get_contents($this->file);
        if ($encode) {
            $this->data = base64_encode($data);
        } else {
            $this->data = $data;
        }
        $this->dataLength = strlen($data);
    }
}
