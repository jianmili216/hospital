<?php
namespace App\Utils;

use Yaf\Config\Ini;

class ConfigLoader
{
    private static $config = NULL;

    public static function get($name = "")
    {
        $path = explode(".", $name);
        $result = self::$config;
        foreach ($path as $node) {
            if (empty($node)) return $result;
            if (!isset($result[$node])) return NULL;
            $result = $result[$node];
        }
        return $result;
    }

    public static function load($config_folder)
    {
        $env = constant('ENV');
        $config_data = new Ini($config_folder . DIRECTORY_SEPARATOR . "application.ini", $env);
        $env_config_data = new Ini($config_folder . DIRECTORY_SEPARATOR . "application.{$env}.ini", $env);
        return self::$config = array_merge($config_data->toArray(), $env_config_data->toArray());
    }
}