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
 * @object     Couch\Document
 * @implements JsonSerializable
 * @author     Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class Document
    implements \JsonSerializable
{
    /**
     * Document ID and document revision ID.
     * @var string, string
     */
    private $id, $rev;

    /**
     * Delete(d) flag.
     * @var bool
     */
    private $deleted = false;

    /**
     * Document attachments.
     * @var array
     */
    private $attachments = array();

    /**
     * Database object.
     * @var Couch\Database
     */
    private $database;

    /**
     * Document data.
     * @var array
     */
    private $data = array();

    /**
     * Object constructor.
     *
     * @param Couch\Database|null $database
     * @param array               $data
     */
    public function __construct(Database $database = null, array $data = array()) {
        if ($database) {
            $this->database = $database;
        }

        if (!empty($data)) {
            $this->setData($data);
        }
    }

    /**
     * Setter method for magic actions.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value) {
        $this->setData([$key => $value]);
    }

    /**
     * Getter method for magic actions.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __get($key) {
        return $this->getData($key);
    }

    /**
     * JSON encoding method of JsonSerializable object.
     *
     * @return array
     */
    public function jsonSerialize() {
        return $this->data;
    }

    /**
     * Set document database.
     *
     * @param  Couch\Database $database
     * @return void
     */
    public function setDatabase(Database $database) {
        $this->database = $database;
    }

    /**
     * Get document database.
     *
     * @return Couch\Database
     */
    public function getDatabase() {
        return $this->database;
    }

    /**
     * Set document ID.
     *
     * @param  Couch\Uuid|string $id
     * @return void
     */
    public function setId($id) {
        if (!$this->id instanceof Uuid) {
            $id = new Uuid($id);
        }
        $this->id = $id;
    }

    /**
     * Set document revision ID.
     *
     * @param  string $id
     * @return void
     */
    public function setRev($rev) {
        $this->rev = $rev;
    }

    /**
     * Set deleted flag.
     *
     * @param  bool $deleted
     * @return void
     */
    public function setDeleted($deleted) {
        $this->deleted = (bool) $deleted;
    }

    /**
     * Get document ID.
     *
     * @return Couch\Uuid|string|null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get document revision ID.
     *
     * @return string|null
     */
    public function getRev() {
        return $this->rev;
    }

    /**
     * Get deleted flag.
     *
     * @return bool
     */
    public function getDeleted() {
        return $this->deleted;
    }

    /**
     * Add an attachment to document object.
     *
     * @param  Couch\DocumentAttachment|array $attachment
     * @return void
     * @throws Couch\Exception
     */
    public function setAttachment($attachment) {
        if (!$attachment instanceof DocumentAttachment) {
            // check file if array given
            if (!isset($attachment['file'])) {
                throw new Exception('Attachment file is required!');
            }

            $file =& $attachment['file'];
            $fileName =& $attachment['file_name'];
            $attachment = new DocumentAttachment($this, $file, $fileName);
        }

        // check if attachment is duplicate
        if (isset($this->data['_attachments'][$attachment->fileName])) {
            throw new Exception('Attachment is alredy exists on this document!');
        }

        // add attachment object using file name as key
        $this->attachments[$attachment->fileName] =
            $this->data['_attachments'][$attachment->fileName] = $attachment;
    }

    /**
     * Get a document attachment by name.
     *
     * @param  string $name
     * @return Couch\DocumentAttachment|null
     */
    public function getAttachment($name) {
        if (isset($this->attachments[$name])) {
            return $this->attachments[$name];
        }
    }

    /**
     * Get all attachments.
     *
     * @return array
     */
    public function getAttachmentAll() {
        return $this->attachments;
    }

    /**
     * Remove a document attachment.
     *
     * @param  string $name
     * @return void
     */
    public function unsetAttachment($name) {
        if (isset($this->attachments[$name])) {
            unset($this->attachments[$name]);
        }
    }

    /**
     * Dump all document attachments.
     *
     * @return void
     */
    public function unsetAttachmentAll() {
        $this->attachments = array();
    }

    /**
     * Set document data.
     *
     * @param array $data
     */
    public function setData(array $data) {
        // set special properties
        if (isset($data['_id']))      $this->setId($data['_id']);
        if (isset($data['_rev']))     $this->setRev($data['_rev']);
        if (isset($data['_deleted'])) $this->setDeleted($data['_deleted']);
        if (isset($data['_attachments'])) {
            // add attachments and remove it so prevent to add into data array
            foreach ($data['_attachments'] as $attachment) {
                $this->setAttachment($attachment);
            }
            unset($data['_attachments']);
        }

        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Get document data value.
     *
     * @param  string $key
     * @return mixed
     */
    public function getData($key = null) {
        if ($key) {
            return Util\Util::dig($key, $this->data);
        }

        return $this->data;
    }

    /**
     * Ping document.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/document/common.html#head--{db}-{docid}
     * @param  int $statusCode Expected status code.
     * @return bool
     */
    public function ping($statusCode = 200) {
        // check id
        if (empty($this->id)) {
            throw new Exception('_id field is could not be empty!');
        }

        $headers = array();
        // check and add if rev provided
        if (!empty($this->rev)) {
            $headers['If-None-Match'] = sprintf('"%s"', $this->rev);
        }

        $response = $this->database->client->head('/'. $this->database->name .'/'. $this->id, null, $headers);

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
        if (!empty($this->rev) && !isset($query['rev'])) {
            $query['rev'] = $this->rev;
        }

        return $this->database->client->get($this->database->name .'/'. $this->id, $query)->getData();
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
        $query = array();
        $query['attachments'] = true;
        $query['att_encoding_info'] = $attEncInfo;
        if ($attsSince)  {
            $attsSinceArray = array();
            foreach ($attsSince as $attsSinceValue) {
                $attsSinceArray[] = sprintf('"%s"', Util\Util::quote($attsSinceValue));
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
        $headers = array();
        $headers['Content-Type'] = 'application/json';
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        $data = $this->getData();
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $name => $attachment) {
                $data['_attachments'][$name] = $attachment->toArray();
            }
        }
        return $this->database->client->post($this->database->name . $batch, null, $data, $headers)->getData();
    }
    // http://docs.couchdb.org/en/1.5.1/api/document/common.html#delete--{db}-{docid}
    public function remove($batch = false, $fullCommit = false) {
        if (empty($this->id) || empty($this->rev)) {
            throw new Exception('Both _id & _rev fields could not be empty!');
        }
        $batch = $batch ? '?batch=ok' : '';
        $headers = array();
        $headers['If-Match'] = $this->rev;
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->database->client->delete($this->database->name .'/'. $this->id . $batch, null, $headers)->getData();
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
        $headers = array();
        $headers['Destination'] = $destination;
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->database->client->copy($this->database->name .'/'. $this->id . $batch, null, $headers)->getData();
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
        $headers = array();
        $headers['If-Match'] = $this->rev;
        $headers['Destination'] = $destination;
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->database->client->copy($this->database->name .'/'. $this->id . $batch, null, $headers)->getData();
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
        $headers = array();
        $headers['If-Match'] = $this->rev;
        $headers['Destination'] = sprintf('%s?rev=%s', $destination, $destinationRevision);
        if ($fullCommit) {
            $headers['X-Couch-Full-Commit'] = 'true';
        }
        return $this->database->client->copy($this->database->name .'/'. $this->id . $batch, null, $headers)->getData();
    }
}
