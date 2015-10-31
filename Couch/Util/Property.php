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

use \Couch\Exception;

/**
 * @package    Couch
 * @subpackage Couch\Util
 * @object     Couch\Util\Property
 * @uses       Couch\Exception
 * @author     Kerem Güneş <qeremy[at]gmail[dot]com>
 */
trait Property
{
    /**
     * Setter method (forbids mutate actions).
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     * @throws Couch\Exception
     */
    public function __set($name, $value) {
        throw new Exception(sprintf(
            '`%s` object is read-only!', get_called_class()));
    }

    /**
     * Getter method.
     *
     * @param  string $name
     * @return mixed
     * @throws Couch\Exception
     */
    public function __get($name) {
        if (!property_exists($this, $name)) {
            throw new Exception(sprintf(
                '`%s` property does not exists on `%s` object!', $name, get_called_class()));
        }

        return $this->{$name};
    }
}
