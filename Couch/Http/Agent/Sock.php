<?php
namespace Couch\Http\Agent;

use \Couch\Http\Request;
use \Couch\Http\Exception;

class Sock
    extends \Couch\Http\Agent
{
    public function run(Request $request) {
        // @tmp
        pre($request->toString());

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
            $headers = array();
            $headers['Host'] = $url['host'] .':'. $url['port'];
            $headers['Connection'] = 'close';
            foreach ($request->headers as $key => $value) {
                // actually remove header command
                if ($value === null) {
                    continue;
                }
                $headers[$key] = $value;
            }

            // http://bugs.php.net/16452
            // http://forums.devnetwork.net/viewtopic.php?f=1&t=113225#p595221
            fwrite($this->link, sprintf("%s %s%s HTTP/1.0\r\n",
                $request->method, $url['path'], $url['query']));
            foreach ($headers as $key => $value) {
                fwrite($this->link, sprintf("%s: %s\r\n", $key, $value));
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

                $this->result .= fread($this->link, 1024);
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
