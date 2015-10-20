<?php
namespace Couch;

class Uuid
{
    const METHOD_RANDOM = 1,
          METHOD_TIMESTAMP = 2,
          METHOD_TIMESTAMP_HEXED = 3;

    const HASH_ALGO_MD5 = 'md5',
          HASH_ALGO_SHA1 = 'sha1',
          HASH_ALGO_CRC32B = 'crc32b';

    private $value;

    public function __construct($value) {
        if ($value === true) {
            $value = self::generate();
        } elseif ($value instanceof \Couch\Object\Server) {
            $value = $value->getUuid();
        }
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
