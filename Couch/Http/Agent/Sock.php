<?php
/**
 * Copyright 2015 Kerem Güneş
 *    <k-gun@mail.com>
 *
 * Apache License, Version 2.0
 *    <http://www.apache.org/licenses/LICENSE-2.0>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Couch\Http\Agent;

use Couch\Http\Request;
use Couch\Http\Exception;

/**
 * @package    Couch
 * @subpackage Couch\Http\Agent
 * @object     Couch\Http\Agent\Sock
 * @uses       Couch\Http\Request
 * @uses       Couch\Http\Exception
 * @author     Kerem Güneş <k-gun@mail.com>
 */
class Sock
   extends \Couch\Http\Agent
{
   /**
    * Run a request using agent socket method.
    *
    * @param  Couch\Http\Request $request
    * @return bool
    * @throws Couch\Http\Exception
    */
   public function run(Request $request)
   {
      $url = parse_url($request->uri);
      if (isset($url['query'])) {
         $url['query'] = '?'. $url['query'];
      } else {
         $url['query'] = '';
      }

      // open socket
      $this->link =@ fsockopen(
         $url['host'],
         $url['port'],
         $this->failCode,
         $this->failText,
         $this->config['timeout']
      );

      if (is_resource($this->link)) {
         $headers = [];
         $headers['Host'] = $url['host'] .':'. $url['port'];
         $headers['Connection'] = 'close';
         foreach ($request->headers as $key => $value) {
            // actually remove header command
            if ($value === null) {
               continue;
            }
            $headers[$key] = $value;
         }

         // use HTTP/1.0 cos of => http://bugs.php.net/16452 :))
         // http://forums.devnetwork.net/viewtopic.php?f=1&t=113225#p595221
         fwrite($this->link, sprintf("%s %s%s HTTP/1.0\r\n",
            $request->method, $url['path'], $url['query']));
         foreach ($headers as $key => $value) {
            fwrite($this->link, sprintf("%s: %s\r\n", $key, $value));
         }
         fwrite($this->link, "\r\n");
         fwrite($this->link, $request->body);

         // set stream option
         stream_set_timeout($this->link, $this->config['timeout']);
         stream_set_blocking($this->link, $this->config['blocking']);
         $meta = stream_get_meta_data($this->link);

         $this->result = '';
         while (!feof($this->link)) {
            // check timeout
            if ($meta['timed_out']) {
               $this->clean();
               throw new Exception('Time out!');
            }

            $this->result .= fread($this->link, 1024);
            $meta = stream_get_meta_data($this->link);
         }

         // cleanize
         $this->clean();

         return true;
      }

      return false;
   }

   /**
    * Clean resource.
    *
    * @return void
    */
   public function clean()
   {
      if (is_resource($this->link)) {
         fclose($this->link);
         $this->link = null;
      }
   }
}
