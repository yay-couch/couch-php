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
namespace Couch;

/**
 * @package Couch
 * @object  Couch\Uuid
 * @author  Kerem Güneş <k-gun@mail.com>
 */
class Uuid
{
   /**
    * Generate methods.
    * @const int
    */
   const METHOD_RANDOM = 1,
         METHOD_TIMESTAMP = 2,
         METHOD_TIMESTAMP_HEXED = 3;

   /**
    * Generate algos.
    * @const string
    */
   const HASH_ALGO_MD5 = 'md5',
         HASH_ALGO_SHA1 = 'sha1',
         HASH_ALGO_CRC32B = 'crc32b';

   /**
    * Uuid value.
    * @var string|int
    */
   private $value;

   /**
    * Object constructor.
    *
    * @param mixed $value
    */
   public function __construct($value)
   {
      // called self.generate method
      if ($value === true) {
         $value = self::generate();
      }
      // request for uuid from the server
      elseif ($value instanceof Server) {
         $value = $value->getUuid();
      }
      // else use the given value

      $this->setValue($value);
   }

   /**
    * Magic method for string acts.
    *
    * @return string
    */
   public function __toString()
   {
      return $this->getValue();
   }

   /**
    * Set value.
    *
    * @param string|int $value
    */
   public function setValue($value)
   {
      $this->value = $value;
   }

   /**
    * Get value.
    *
    * @return string|int
    */
   public function getValue()
   {
      return (string) $this->value;
   }

   /**
    * Generate a UUID using method & algo.
    *
    * @param  int   $method
    * @param  string $algo
    * @return string|int
    * @todo   Implement RFC-4122
    */
   public static function generate($method = self::METHOD_RANDOM, $algo = self::HASH_ALGO_MD5)
   {
      switch ($method) {
         // random also as default
         case self::METHOD_RANDOM:
            // check availability of given algo
            if (!in_array($algo, hash_algos())) {
               $algo = self::HASH_ALGO_MD5;
            }

            // care of mcrypt const
            $rand = MCRYPT_RAND;
            if (defined('MCRYPT_DEV_URANDOM')) {
               $rand = MCRYPT_DEV_URANDOM;
            } elseif (defined('MCRYPT_DEV_RANDOM')) {
               $rand = MCRYPT_DEV_RANDOM;
            }

            // use mcrypt & hash it
            $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
            $uuid = hash($algo, mcrypt_create_iv($size, $rand));
            break;

         // simply unixtime
         case self::METHOD_TIMESTAMP:
            $uuid = time();
            break;

         // simply unixtime (hexed)
         case self::METHOD_TIMESTAMP_HEXED:
            $uuid = base_convert(time(), 10, 16);
            break;

         // ops!
         default:
            throw new Exception('Unimplemented method given!');
      }

      return $uuid;
   }
}
