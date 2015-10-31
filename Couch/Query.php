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

class Query
{
    private $database;
    private $data = array(),
            $dataString = '';

    public function __construct(Database $database = null, array $data = array()) {
        if ($database) {
            $this->database = $database;
        }
        if (!empty($data)) {
            $this->data = $data;
        }
    }
    public function __toString() {
        return $this->toString();
    }

    public function setDatabase(Database $database) {
        $this->database = $database;
    }
    public function getDatabase() {
        return $this->database;
    }

    public function run() {
        if (!$this->database) {
            throw new Exception(sprintf(
                'Set database first on init or calling %s::setDatabase() to run a request!', __class__));
        }
        return $this->database->getDocumentAll($this->data);
    }

    public function set($key, $value) {
        $key = strtolower(trim($key));
        $this->data[$key] = $value;
        return $this;
    }
    public function get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
    }

    public function toArray() {
        return $this->data;
    }

    public function toString() {
        if (!empty($this->dataString)) {
            return $this->dataString;
        }
        $data = array();
        foreach ($this->data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $data[] = sprintf('%s=%s', $key, urlencode($value));
        }
        return ($this->dataString = join('&', $data));
    }

    public function skip($num) {
        $this->data['skip'] = $num;
        return $this;
    }
    public function limit($num) {
        $this->data['limit'] = $num;
        return $this;
    }
}
