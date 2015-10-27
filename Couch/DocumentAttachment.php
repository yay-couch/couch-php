<?php
/**
 * Copyright 2015 Kerem Güneş
 *     <http://qeremy.com>
 *
 * Apache License, Version 2.0
 *     <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Couch;

/**
 * @package Couch
 * @object  Couch\DocumentAttachment
 * @author  Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class DocumentAttachment
{
    /**
     * Owner document.
     * @var Couch\Document
     */
    private $document;

    /**
     * Abstract file path & file name.
     * @var string, string
     */
    private $file, $fileName;

    /**
     * Attachment file contents, contents length.
     * @var string, int
     */
    private $data, $dataLength;

    /**
     * Attachment mime.
     * @var string
     */
    private $contentType;

    /**
     * CouchDB file digest.
     * @var string
     */
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
        if ($name == 'file') {
            $this->file = $value;
            $this->fileName = basename($value);
            return;
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

    // http://docs.couchdb.org/en/1.5.1/api/document/attachments.html#head--{db}-{docid}-{attname}
    public function ping($statusCode = 200) {
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }
        $docId = $this->document->getId();
        $docRev = $this->document->getRev();
        if (empty($docId)) {
            throw new Exception('Attachment document _id is required!');
        }
        if (empty($this->fileName)) {
            throw new Exception('Attachment file name is required!');
        }
        $query = $headers = array();
        if (!empty($docRev)) {
            $query['rev'] = $docRev;
            // cancel using rev in headers @see https://issues.apache.org/jira/browse/COUCHDB-2860
            // $headers['If-Match'] = $docRev;
        }
        if (!empty($this->digest)) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->digest);
        }

        $database = $this->document->getDatabase();
        $response = $database->client->head(sprintf('%s/%s/%s',
            $database->name, $docId, $this->fileName), $query, $headers);
        return in_array($response->getStatusCode(), (array) $statusCode);
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/attachments.html#get--{db}-{docid}-{attname}
    public function find() {
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }
        $docId = $this->document->getId();
        $docRev = $this->document->getRev();
        if (empty($docId)) {
            throw new Exception('Attachment document _id is required!');
        }
        if (empty($this->fileName)) {
            throw new Exception('Attachment file name is required!');
        }
        $query = $headers = array();
        if (!empty($docRev)) {
            // cancel using rev in headers @see https://issues.apache.org/jira/browse/COUCHDB-2860
            // $headers['If-Match'] = $docRev;
            $query['rev'] = $docRev;
        }
        $headers['Accept'] = '*/*';
        $headers['Content-Type'] = null;
        if (!empty($this->digest)) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->digest);
        }
        $database = $this->document->getDatabase();
        $response = $database->client->get(sprintf('%s/%s/%s',
            $database->name, $docId, urlencode($this->fileName)), $query, $headers);
        if (in_array($response->getStatusCode(), [200, 304])) {
            $return = array();
            $return['content'] = $response->getData();
            $return['content_type'] = $response->getHeader('Content-Type');
            $return['content_length'] = $response->getHeader('Content-Length');
            if ($md5 = $response->getHeader('Content-MD5')) {
                $return['digest'] = 'md5-'. $md5;
            } else {
                $return['digest'] = 'md5-'. trim($response->getHeader('ETag'), '"');
            }
            return $return;
        }
    }
     // http://docs.couchdb.org/en/latest/api/document/attachments.html#put--db-docid-attname
    public function save() {
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }
        $docId = $this->document->getId();
        $docRev = $this->document->getRev();
        if (empty($docId)) {
            throw new Exception('Attachment document _id is required!');
        }
        if (empty($docRev)) {
            throw new Exception('Attachment document _rev is required!');
        }
        if (empty($this->fileName)) {
            throw new Exception('Attachment file name is required!');
        }
        $this->readFile(false);
        $headers = array();
        $headers['If-Match'] = $docRev;
        $headers['Content-Type'] = $this->contentType;

        $database = $this->document->getDatabase();
        return $database->client->put(sprintf('%s/%s/%s',
            $database->name, $docId, urlencode($this->fileName)
        ), null, $this->data, $headers)->getData();
    }
    // http://docs.couchdb.org/en/latest/api/document/attachments.html#delete--db-docid-attname
    public function remove($batch = false, $fullCommit = false) {
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }
        $docId = $this->document->getId();
        $docRev = $this->document->getRev();
        if (empty($docId)) {
            throw new Exception('Attachment document _id is required!');
        }
        if (empty($docRev)) {
            throw new Exception('Attachment document _rev is required!');
        }
        if (empty($this->fileName)) {
            throw new Exception('Attachment file name is required!');
        }
        $batch = $batch ? '?batch=ok' : '';
        $headers = array();
        $headers['If-Match'] = $docRev;
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }

        $database = $this->document->getDatabase();
        return $database->client->delete(sprintf('%s/%s/%s%s',
            $database->name, $docId, urlencode($this->fileName), $batch
        ), null, $headers)->getData();
    }

    public function toArray($encode = true) {
        $this->readFile($encode);
        $array = array();
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
