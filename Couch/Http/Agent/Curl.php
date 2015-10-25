<?php
namespace Couch\Http\Agent;

use \Couch\Http\Request;
use \Couch\Http\Exception;

class Curl
    extends \Couch\Http\Agent
{
    public function run(Request $request) {
        if (!extension_loaded('curl')) {
            throw new Exception('cURL extension not found!');
        }

        $this->link =@ curl_init($request->uri);
        if (is_resource($this->link)) {
            $headers = array();
            // proper response headers/body pairs
            $headers[] = 'Expect: ';
            foreach ($request->headers as $key => $value) {
                // actually remove header command
                if ($value === null) {
                    continue;
                }
                $headers[] = sprintf('%s: %s', $key, $value);
            }

            // cURL options
            $options = array(
                CURLOPT_CUSTOMREQUEST  => $request->method,
                CURLOPT_HEADER         => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_CONNECTTIMEOUT => $this->config['timeout'],
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLINFO_HEADER_OUT    => true
            );

            if ($request->method == Request::METHOD_HEAD) {
                $options[CURLOPT_NOBODY] = true;
                $options[CURLOPT_FOLLOWLOCATION] = true;
            } else {
                $options[CURLOPT_RETURNTRANSFER] = true;
                if ($request->method != Request::METHOD_GET) {
                    $options[CURLOPT_POSTFIELDS] = $request->body;
                }
            }

            curl_setopt_array($this->link, $options);

            // prevent output whole reponse if NOBODY=1
            ob_start();
            $result =@ curl_exec($this->link);
            $this->result = ob_get_clean();
            if (is_string($result)) {
                $this->result = $result;
            }

            if ($this->result === false) {
                $this->failCode = curl_errno($this->link);
                $this->failText = curl_error($this->link);

                $this->clean();

                return false;
            }

            $this->clean();

            return true;
        }

        return false;
    }

    public function clean() {
        if (is_resource($this->link)) {
            curl_close($this->link);
            $this->link = null;
        }
    }
}
