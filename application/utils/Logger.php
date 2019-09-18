<?php
/**
 * Created by PhpStorm.
 * User: weijin
 * Date: 17/3/10
 * Time: 17:57
 */

namespace App\Utils;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;

class Logger
{
    private $logger;
    private static $_instance;

    private function __construct($log_index)
    {
        $this->logger = new MonologLogger($log_index);
        $this->create_file($log_index);
        // 根据日志级别判断是否添加扩展字段(默认所有级别都添加扩展字段)
        $int = new IntrospectionProcessor(MonologLogger::DEBUG, [], 1);
        $this->logger->pushProcessor(function ($record) use ($int) {
            return $int->__invoke($record);
        });
    }

    // 默认是系统错误
    public static function getInstance($log_index = 'application')
    {
        $log_index = $log_index . '/' . date('Ym');
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($log_index);
        }
        return self::$_instance;
    }

    private function create_file($log_index)
    {
        $path = rtrim(constant('LOG_PATH_PREFIX'), '/');
        $filename = $path . '/' . $log_index . '/' . date("d") . '.log';
        $this->handler($filename);
    }

    private static function format()
    {
        $output = "[%datetime%] [%level_name%] %message% [FromClass: %extra.class%]" . PHP_EOL;
        return new LineFormatter($output);
    }

    private function handler($file_name)
    {
        $stream = new StreamHandler($file_name);
        $stream->setFormatter(self::format());
        if (!$this->logger->getHandlers()) {
            $this->logger->pushHandler($stream);
        }
    }

    public function save($msg, $level = 'info')
    {
        $setting_level = constant('LOG_LEVEL');
        switch ($setting_level) {
            case 'debug':
                $allow_level = ['debug', 'info', 'error'];
                break;
            case 'info':
                $allow_level = ['info', 'error'];
                break;
            case 'error':
                $allow_level = ['error'];
                break;
            default:
                $allow_level = ['debug', 'info', 'error'];
        }
        if (in_array($level, $allow_level)) {
            $msg = is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE) : $msg;
            $monolog_level = MonologLogger::toMonologLevel($level);
            $this->logger->addRecord($monolog_level, $msg);
        }
    }
}