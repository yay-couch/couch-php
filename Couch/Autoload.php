<?php
namespace Couch;

class Autoload
{
    private static $instance;

    private function __clone() {}
    private function __construct() {}

    public static function init() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function register() {
        spl_autoload_register(function($name) {
            // fix root
            if ($name[0] != '\\') {
                $name = '\\'. $name;
            }

            // only couch files
            if (1 !== strpos($name, __namespace__)) {
                return;
            }

            // prepare file name & path
            $name = substr($name, 1 + strlen(__namespace__));
            $name = str_replace('\\', '/', $name);
            $file = sprintf('%s/%s.php', __dir__, $name);

            require($file);
        });
    }
}

// auto init for including
return Autoload::init();
