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
namespace Couch\Util;

/**
 * @package    Couch
 * @subpackage Couch\Util
 * @object     Couch\Util\Util
 * @author     Kerem Güneş <k-gun@mail.com>
 */
abstract class Util
{
   /**
    * Pagination method that calculates skip param.
    *
    * @param  int $offset
    * @param  int $limit
    * @return int
    */
   public static function getSkip($offset, $limit)
   {
      $page = ($offset / $limit) + 1;
      $skip = $page * $limit;
      return $skip;
   }

   /**
    * Quote method.
    *
    * @param  string $input
    * @return string
    */
   public static function quote($input)
   {
      return str_replace('"', '%22', $input);
   }

   /**
    * Array fetch method.
    *
    * @param  mixed $key
    * @param  array $array
    * @param  mixed $valueDefault
    * @return mixed
    */
   public static function dig($key, array $array, $valueDefault = null)
   {
      // direct access
      if (isset($array[$key])) {
         $value =& $array[$key];
      }
      // trace element path
      else {
         $value =& $array;
         foreach (explode('.', $key) as $key) {
            $value =& $value[$key];
         }
      }

      return ($value !== null) ? $value : $valueDefault;
   }
}
