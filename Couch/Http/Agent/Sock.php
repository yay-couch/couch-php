<?php
namespace Couch\Http\Agent;

use \Couch\Http\Request;

class Sock
    extends \Couch\Http\Agent
{
    public function run(Request $request) {
        $url = parse_url($request->uri);
        if (isset($url['query'])) {
            $url['query'] = '?'. $url['query'];
        } else {
            $url['query'] = '';
        }

        $this->link =@ fsockopen(
            $url['host'],
            $url['port'],
            $this->failCode,
            $this->failText,
            $this->config['timeout']
        );

        if (is_resource($this->link)) {
            $headers = [];
            $headers['Host'] = $url['host'];
            $headers['Connection'] = 'close';
            $headers = $headers + $request->headers;

            fwrite($this->link, sprintf("%s %s%s HTTP/1.1\r\n",
                $request->method, $url['path'], $url['query']));
            foreach ($headers as $key => $val) {
                fwrite($this->link, sprintf("%s: %s\r\n", $key, $val));
            }
            fwrite($this->link, "\r\n");
            fwrite($this->link, $request->body);

            stream_set_timeout($this->link, $this->config['timeout']);
            stream_set_blocking($this->link, $this->config['blocking']);
            $meta = stream_get_meta_data($this->link);

            $this->result = '';
            while (!feof($this->link)) {
                if ($meta['timed_out']) {

                    $this->clean();

                    throw new Exception('Time out!');
                }

                $this->result .= fgets($this->link, 1024);
                $meta = stream_get_meta_data($this->link);
            }

            $this->clean();

            return true;
        }

        return false;
    }

    public function clean() {
        if (is_resource($this->link)) {
            fclose($this->link);
            $this->link = null;
        }
    }
}
