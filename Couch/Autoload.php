<?php
/**
 * Copyright 2015 Kerem Güneş
 *    <http://qeremy.com>
 *
 *Apache License, Version 2.0
 *    <http://www.apache.org/licenses/LICENSE-2.0>
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 */
namespace Couch;

/**
 * @package Couch
 * @object  Couch\Autoload
 * @author  Kerem Güneş <qeremy[at]gmail[dot]com>
 */
class Autoload
{
    /**
     * Singleton stuff.
     * @var Couch\Autoload
     */
    private static $instance;

    /**
     * Forbid initialization & clone.
     * @return void, void
     */
    private function __clone() {}
    private function __construct() {}

    /**
     * Static initializer.
     * @return Couch\Autoload
     */
    public static function init() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registerer.
     * @return void
     */
    public function register() {
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

// auto-init for including
return Autoload::init();
