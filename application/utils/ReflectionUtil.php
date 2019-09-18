<?php
namespace App\Utils;

class ReflectionUtil
{
    static public function createInstanceByArray($classname, $param)
    {
        $reflectClass = new \ReflectionClass($classname);
        $reflectMethod = $reflectClass->getConstructor();

        $args = array();
        foreach ($reflectMethod->getParameters() as $parameter) {
            $args[] = self::fetchParameter($param, $parameter);
        }

        return $reflectClass->newInstanceArgs($args);
    }

    static public function fetchParameter($data, $parameter)
    {
        if (isset($data[$parameter->getName()])) {
            return $data[$parameter->getName()];
        }

        if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $param_name = $parameter->getName();
        $data = json_encode($data);
        throw new \Exception("fetchParameter fail, name: $param_name, data: $data");
    }
}