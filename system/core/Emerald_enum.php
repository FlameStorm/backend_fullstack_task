<?php
/*
 * ------------------------------------------------------
 *  Makes easy to work with Enums :)
 * ------------------------------------------------------
 */
namespace System\Core;

use ReflectionClass;

class Emerald_enum
{
    private $name;
    private $value;

    public static function get_list(): array
    {
        return (new ReflectionClass(static::class))->getConstants();
    }

    public static function has_value($value): bool
    {
        return in_array($value, static::get_list());
    }

    public static function has_name($name): bool
    {
        return array_key_exists($name, static::get_list());
    }

    private function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Factory constructing the constant
     *
     * @param string $name
     * @param array $arguments
     * @return static
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (!static::has_name($name)) {
            throw new \Exception("Unknown constant {$name}");
        }
        return new static($name, constant("static::{$name}"));
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function get_value()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
