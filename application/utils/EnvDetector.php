<?php
namespace App\Utils;

class EnvDetector
{

    private static $test_host = array(
        "test-001",
    );

    private static $product_hosts = array(
        "web-095",
    );

    public static function get()
    {
        $hostname = php_uname('n');
        if (in_array($hostname, self::$product_hosts)) {
            return "product";
        } else if (in_array($hostname, self::$test_host)) {
            return "test";
        } else {
            ini_set('display_errors', 'on');
            ini_set('track_errors', 1);
            return "development";
        }
    }
}