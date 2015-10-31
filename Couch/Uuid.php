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
 * @object  Couch\Uuid
 * @author  Kerem Güneş <qeremy[at]gmail[dot]com>
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
    public function __construct($value) {
        // called self.generate method
        if ($value === true) {
            $value = self::generate();
        }
        // request for uuid from the server
        elseif ($value instanceof Server) {
            $value = $value->getUuid();
        }
        // use given value
        // else {}

        $this->setValue($value);
    }

    public function __toString() {
        return $this->getValue();
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public static function generate($method = self::METHOD_RANDOM, $algo = self::HASH_ALGO_MD5) {
        switch ($method) {
            case self::METHOD_TIMESTAMP:
                $uuid = time();
                break;
            case self::METHOD_TIMESTAMP_HEXED:
                $uuid = base_convert(time(), 10, 16);
                break;
            case self::METHOD_RANDOM:
            default:
                if (!in_array($algo, hash_algos())) {
                    $algo = self::HASH_ALGO_MD5;
                }

                $rand = MCRYPT_RAND;
                if (defined('MCRYPT_DEV_URANDOM')) {
                    $rand = MCRYPT_DEV_URANDOM;
                } elseif (defined('MCRYPT_DEV_RANDOM')) {
                    $rand = MCRYPT_DEV_RANDOM;
                }

                $size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
                $uuid = hash($algo, mcrypt_create_iv($size, $rand));
                break;
        }

        return $uuid;
    }
}
