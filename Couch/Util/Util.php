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
namespace Couch\Util;

abstract class Util
{
    public static function getSkip($offset, $limit) {
        $page = ($offset / $limit) + 1;
        $skip = $page * $limit;
        return $skip;
    }

    public static function quote($input) {
        return str_replace('"', '%22', $input);
    }

    public static function dig($key, array $array, $defaultValue = null) {
        return array_key_exists($key, $array)
            ? $array[$key] : $defaultValue;
    }
}
