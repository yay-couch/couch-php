<?php
namespace Couch;

use \Couch\Http\Agent\Sock,
    \Couch\Http\Agent\Curl;

class Couch
{
    const VERSION = '1.0';

    const HTTP_AGENT_SOCK = 'Sock',
          HTTP_AGENT_CURL = 'Curl';

    private $httpAgent;
    private $httpAgentDefault = self::HTTP_AGENT_SOCK;

    public function __construct($httpAgent = null, array $config = []) {
        if ($httpAgent == null) {
            // default agent
            $httpAgent = '\\Couch\\Http\\Agent\\'. $this->httpAgentDefault;
            $this->httpAgent = new $httpAgent($config);
        } elseif ($httpAgent == self::HTTP_AGENT_SOCK || $httpAgent == self::HTTP_AGENT_CURL) {
            $httpAgent = '\\Couch\\Http\\Agent\\'. $httpAgent;
            $this->httpAgent = new $httpAgent($config);
        } elseif ($httpAgent instanceof Sock || $httpAgent instanceof Curl) {
            $this->httpAgent = $httpAgent;
        }
    }

    public function getHttpAgent() {
        return $this->httpAgent;
    }

    public function getHttpAgentDefault() {
        return $this->httpAgentDefault;
    }
}
