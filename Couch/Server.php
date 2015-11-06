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
 * @object  Couch\Server
 * @author  Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class Server
{
    /**
     * Client object.
     * @var Couch\Client
     */
    private $client;

    /**
     * Object constructor.
     *
     * @param Couch\Client $client
     */
    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * Ping server.
     * @return bool
     */
    public function ping() {
        return (200 === $this->client->head('/')->getStatusCode());
    }

    /**
     * Get server info.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#api-server-root
     * @param  string $key
     * @return mixed
     */
    public function info($key = null) {
        $info = $this->client->get('/')->getData();
        if ($key) {
            return Util\Util::dig($key, $info);
        }
        return $info;
    }

    /**
     * Get server version.
     *
     * @return string
     */
    public function version() {
        return $this->info('version');
    }

    /**
     * Get active tasks.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#active-tasks
     * @return array
     */
    public function getActiveTasks() {
        return $this->client->get('/_active_tasks')->getData();
    }

    /**
     * Get all databases.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#all-dbs
     * @return array
     */
    public function getAllDatabases() {
        return $this->client->get('/_all_dbs')->getData();
    }

    /**
     * Get all database events.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#get--_db_updates
     * @param  array|string $query
     * @return array
     */
    public function getDatabaseUpdates($query = null) {
        return $this->client->get('/_db_updates', $query)->getData();
    }

    /**
     * Get server logs.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#log
     * @param  array|string $query
     * @return string
     */
    public function getLogs($query = null) {
        return $this->client->get('/_log', $query)->getBody();
    }

    /**
     * Request, configure, or stop, a replication operation.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#replicate
     * @param  array $query
     * @return array
     */
    public function replicate(array $query) {
        if (!isset($query['source'], $query['target'])) {
            throw new Exception('Both source & target required!');
        }

        return $this->client->post('/_replicate', null, $query)->getData();
    }

    /**
     * Restarts the CouchDB instance.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#restart
     * @note   You must be authenticated as a user with administration privileges for this to work.
     * @return bool
     */
    public function restart() {
        return (202 === $this->client->post('/_restart')->getStatusCode());
    }

    /**
     * Get server stats.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#stats
     * @param  string $path
     * @return array
     */
    public function getStats($path = null) {
        return $this->client->get('/_stats/'. $path)->getData();
    }

    /**
     * Get new uuid(s).
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/common.html#uuids
     * @param  int $count
     * @return string|array
     */
    public function getUuid($count = 1) {
        $data = $this->client->get('/_uuids', ['count' => $count])->getData('uuids');
        return ($count === 1) ? $data[0] : $data;
    }

    /**
     * Get config(s).
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/configuration.html#get--_config
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/configuration.html#get--_config-{section}
     * @param  string $section
     * @param  string $key
     * @return mixed
     */
    public function getConfig($section = null, $key = null) {
        $path = join('/', array_filter([$section, $key]));

        return $this->client->get('/_config/'. $path)->getData();
    }

    /**
     * Set a config value.
     *
     * @link  http://docs.couchdb.org/en/1.5.1/api/server/configuration.html#put--_config-{section}-{key}
     * @param string $section
     * @param string $key
     * @param mixed  $value
     */
    public function setConfig($section, $key, $value) {
        $path = join('/', [$section, $key]);
        $response = $this->client->put('/_config/'. $path, null, $value);
        return (200 === $response->getStatusCode())
            ? $response->getData() : false;
    }

    /**
     * Delete a config.
     *
     * @link   http://docs.couchdb.org/en/1.5.1/api/server/configuration.html#delete--_config-{section}-{key}
     * @param  string $section
     * @param  string $key
     * @return mixed
     */
    public function removeConfig($section, $key) {
        $path = join('/', [$section, $key]);
        $response = $this->client->delete('/_config/'. $path);
        return (200 === $response->getStatusCode())
            ? $response->getData() : false;
    }
}
