<?php
namespace Couch\Util;

trait PropertyTrait
{
    /**
     * Forbid mutate actions.
     *
     * @param  string $name
     * @param  any    $value
     * @return void
     * @throws \Exception
     */
    public function __set($name, $value) {
        throw new \Exception(sprintf(
            '`%s` object is readonly', get_called_class()));
    }

    /**
     * Getter method.
     *
     * @param  string $name
     * @return any
     * @throws \Exception
     */
    public function __get($name) {
        if (!property_exists($this, $name)) {
            throw new \Exception(sprintf(
                '`%s` property does not exists on `%s` object!', $name, get_called_class()));
        }

        return $this->{$name};
    }
}
