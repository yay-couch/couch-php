<?php
/**
 * Copyright 2015 Kerem Güneş
 *    <http://qeremy.com>
 *
 *Apache License, Version 2.0
 *    <http://www.apache.org/licenses/LICENSE-2.0>
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 */
namespace Couch;

use \Couch\Http\Agent\Sock,
    \Couch\Http\Agent\Curl;

/**
 * @package Couch
 * @object  Couch\Couch
 * @uses    Couch\Http\Agent\Sock,
 *          Couch\Http\Agent\Curl
 * @author  Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class Couch
{
    /**
     * Couch version
     * @const string
     */
    const VERSION = '1.0';

    /**
     * HTTP agent name.
     * @const string
     */
    const HTTP_AGENT_SOCK = 'Sock',
          HTTP_AGENT_CURL = 'Curl';

    /**
     * HTTP agent that will be used for all requests.
     * @var Couch\Http\Agent
     */
    private $httpAgent;

    /**
     * Default HTTP agent name.
     * @var string
     */
    private $httpAgentDefault = self::HTTP_AGENT_SOCK;

    /**
     * Object constructor.
     *
     * @param  string|Couch\Http\Agent $httpAgent
     * @param  array                   $config
     * @throws Couch\Exception
     */
    public function __construct($httpAgent = null, array $config = array()) {
        if ($httpAgent == null) {
            // default agent
            $httpAgent = '\\Couch\\Http\\Agent\\'. $this->httpAgentDefault;
            $this->httpAgent = new $httpAgent($config);
        } elseif ($httpAgent == self::HTTP_AGENT_SOCK || $httpAgent == self::HTTP_AGENT_CURL) {
            $httpAgent = '\\Couch\\Http\\Agent\\'. $httpAgent;
            $this->httpAgent = new $httpAgent($config);
        } elseif ($httpAgent instanceof Sock || $httpAgent instanceof Curl) {
            $this->httpAgent = $httpAgent;
        } else {
            throw new Exception('Unknown HTTP agent given!');
        }
    }

    /**
     * Get HTTP agent.
     *
     * @return Couch\Http\Agent
     */
    public function getHttpAgent() {
        return $this->httpAgent;
    }

    /**
     * Get default HTTP agent name.
     *
     * @return string|null
     */
    public function getHttpAgentDefault() {
        return $this->httpAgentDefault;
    }
}
