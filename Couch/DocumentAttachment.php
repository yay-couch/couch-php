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
 * @package    Couch
 * @object     Couch\DocumentAttachment
 * @implements JsonSerializable
 * @author     Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class DocumentAttachment
    implements \JsonSerializable
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

    /**
     * Object constructor.
     *
     * @param Couch\Document|null $document
     * @param string|null         $file
     * @param string|null         $fileName
     */
    public function __construct(Document $document = null, $file = null, $fileName = null) {
        if ($document) {
            $this->document = $document;
        }

        if (!empty($file)) {
            $this->file = $file;
            if (!empty($fileName)) {
                // set filename if provided
                $this->fileName = $fileName;
            } else {
                // extract filename
                $this->fileName = basename($file);
            }
        }
    }

    /**
     * Setter for magic actions.
     *
     * @param  string $name
     * @param  int    $value
     * @return void
     * @throws Couch\Exception
     */
    public function __set($name, $value) {
        if (!property_exists($this, $name)) {
            throw new Exception(sprintf(
                '`%s` property does not exists on this object!', $name));
        }

        // file is exception
        if ($name == 'file') {
            $this->file = $value;
            $this->fileName = basename($value);
        } else {
            $this->{$name} = $value;
        }
    }

    /**
     * Getter for magic actions.
     *
     * @param  string $name
     * @return mixed
     * @throws Couch\Exception
     */
    public function __get($name) {
        if (!property_exists($this, $name)) {
            throw new Exception(sprintf(
                '`%s` property does not exists on this object!', $name));
        }

        return $this->{$name};
    }

    /**
     * JSON encoding method of JsonSerializable object.
     *
     * @return array
     */
    public function jsonSerialize() {
        return $this->toArray();
    }

    /**
     * Set owner document.
     *
     * @param  Couch\Document $document
     * @return void
     */
    public function setDocument(Document $document) {
        $this->document = $document;
    }

    /**
     * Get owner document.
     *
     * @return Couch\Document
     */
    public function getDocument() {
        return $this->document;
    }

    /**
     * Ping a document attachment.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/document/attachments.html#head--{db}-{docid}-{attname}
     * @param  int    $statusCode Expected status code
     * @return bool
     * @throws Couch\Exception
     */
    public function ping($statusCode = 200) {
        // check owner document
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }

        $docId = $this->document->getId();
        $docRev = $this->document->getRev();

        // check owner document's id
        if (empty($docId)) {
            throw new Exception('Attachment document _id is required!');
        }
        // check filename
        if (empty($this->fileName)) {
            throw new Exception('Attachment file name is required!');
        }

        $query = $headers = array();
        if (!empty($docRev)) {
            $query['rev'] = $docRev;
            // cancel using rev in headers @see https://issues.apache.org/jira/browse/COUCHDB-2860
            // $headers['If-Match'] = $docRev;
        }

        // add digest if provided
        if (!empty($this->digest)) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->digest);
        }

        $database = $this->document->getDatabase();
        $response = $database->client->head(sprintf('%s/%s/%s',
            $database->name, urlencode($docId), urlencode($this->fileName)), $query, $headers);

        return in_array($response->getStatusCode(), (array) $statusCode);
    }

    /**
     * find the file attachment associated with the document.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/document/attachments.html#get--{db}-{docid}-{attname}
     * @return mixed|null
     * @throws Couch\Exception
     */
    public function find() {
        // check owner document
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }

        $docId = $this->document->getId();
        $docRev = $this->document->getRev();

        // check owner document's id
        if (empty($docId)) {
            throw new Exception('Attachment document _id is required!');
        }
        // check filename
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

        // add digest if provided
        if (!empty($this->digest)) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->digest);
        }

        $database = $this->document->getDatabase();
        $response = $database->client->get(sprintf('%s/%s/%s',
            $database->name, urlencode($docId), urlencode($this->fileName)), $query, $headers);

        // check response status code
        if (in_array($response->getStatusCode(), [200, 304])) {
            $return = array();
            $return['content'] = $response->getData();
            $return['content_type'] = $response->getHeader('Content-Type');
            $return['content_length'] = $response->getHeader('Content-Length');
            // add digest info to return
            if ($md5 = $response->getHeader('Content-MD5')) {
                $return['digest'] = 'md5-'. $md5;
            } else {
                $return['digest'] = 'md5-'. trim($response->getHeader('ETag'), '"');
            }
            return $return;
        }
    }

    /**
     * Put the supplied content as an attachment to the owner document.
     *
     * @link   http://docs.couchdb.org/en/latest/api/document/attachments.html#put--db-docid-attname
     * @return mixed
     * @throws Couch\Exception
     */
    public function save() {
        // check owner document
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }

        $docId = $this->document->getId();
        $docRev = $this->document->getRev();

        // check owner document's id
        if (empty($docId)) {
            throw new Exception('Attachment document _id is required!');
        }
        // check owner document's rev
        if (empty($docRev)) {
            throw new Exception('Attachment document _rev is required!');
        }
        // check filename
        if (empty($this->fileName)) {
            throw new Exception('Attachment file name is required!');
        }

        // read file data
        $this->readFile(false);

        $headers = array();
        $headers['If-Match'] = $docRev;
        $headers['Content-Type'] = $this->contentType;

        $database = $this->document->getDatabase();

        return $database->client->put(sprintf('%s/%s/%s',
            $database->name, urlencode($docId), urlencode($this->fileName)
        ), null, $this->data, $headers)->getData();
    }

    /**
     * Delete attachment.
     *
     * @link   http://docs.couchdb.org/en/latest/api/document/attachments.html#delete--db-docid-attname
     * @param  bool   $batch
     * @param  bool   $fullCommit
     * @return mixed
     * @throws Couch\Exception
     */
    public function remove($batch = false, $fullCommit = false) {
        // check owner document
        if (!isset($this->document)) {
            throw new Exception('Attachment document is not defined!');
        }

        $docId = $this->document->getId();
        $docRev = $this->document->getRev();

        // check owner document's id
        if (empty($docId)) {
            throw new Exception('Attachment document _id is required!');
        }
        // check owner document's rev
        if (empty($docRev)) {
            throw new Exception('Attachment document _rev is required!');
        }
        // check filename
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
            $database->name, urlencode($docId), urlencode($this->fileName), $batch
        ), null, $headers)->getData();
    }

    /**
     * Get attachment data as array that CouchDB expects.
     *
     * @param  bool $encode Whether 64 encode file contents.
     * @return array
     */
    public function toArray($encode = true) {
        // read file first
        $this->readFile($encode);

        // set post data
        $array = array();
        $array['data'] = $this->data;
        $array['content_type'] = $this->contentType;

        return $array;
    }

    /**
     * Get attachment data as json string that CouchDB expects.
     *
     * @param  bool $encode Whether 64 encode file contents.
     * @return string
     */
    public function toJson($encode = true) {
        return json_encode($this->toArray($encode));
    }

    /**
     * Read file contents, set attachment data, data length and content type.
     *
     * @param  bool $encode
     * @return void
     * @throws Couch\Exception
     */
    public function readFile($encode = true) {
        // check file
        if (empty($this->file)) {
            throw new Exception('Attachment file is empty!');
        }

        // detect content type
        $type = finfo_file(($info = finfo_open(FILEINFO_MIME_TYPE)), $this->file);
        finfo_close($info);
        if (!$type) {
            throw new Exception("Could not open file `{$this->file}`!");
        }
        $this->contentType = $type;

        $data = file_get_contents($this->file);
        // whether base64 encoding or not?
        if ($encode) {
            $this->data = base64_encode($data);
        } else {
            $this->data = $data;
        }

        // set data length
        $this->dataLength = strlen($data);
    }
}
