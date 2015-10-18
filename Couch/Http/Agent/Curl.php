<?php
namespace Couch\Http\Agent;

use \Couch\Http\Request;

class Curl
    extends \Couch\Http\Agent
{
    public function run(Request $request) {
        if (!extension_loaded('curl')) {
            throw new \Exception('cURL extension not found!');
        }

        $this->link =@ curl_init($request->uri);
        if (is_resource($this->link)) {
            $headers = [];
            // proper response headers/body pairs
            $headers[] = 'Expect: ';
            foreach ($request->headers as $key => $value) {
                $headers[] = sprintf('%s: %s', $key, $value);
            }

            curl_setopt_array($this->link, [
                CURLOPT_CUSTOMREQUEST  => Request::METHOD_GET,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_USERAGENT      => $request->headers['User-Agent'],
                CURLINFO_HEADER_OUT    => true
            ]);

            $theResponse =@ curl_exec($this->link);
            if ($theResponse === false) {
                $this->failCode = curl_errno($this->link);
                $this->failText = curl_error($this->link);
            }

            if ($this->failText !== '') {
                throw new \Exception($this->failText, $this->failCode);
            }

            curl_close($this->link);
            $this->link = null;

            return $theResponse;
        }

        return false;
    }
}
