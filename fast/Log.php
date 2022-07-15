<?php
declare (strict_types=1);

namespace fast;

use fast\log\LogDriver;

class Log
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    const SQL = 'sql';

    /**
     * @var LogDriver[] ;
     */
    protected static array $_instance = [];

    /**
     * 获取命名空间
     * @return string
     */
    public static function getNamespace(): string
    {
        return "fast\\log\\driver";
    }

    /**
     * 单例
     * @param string $type
     * @return LogDriver
     * @throws Exception
     */
    public static function instance($type = "default"): LogDriver
    {
        if ($type === "default") {
            $_type = Config::get("Log.default");
        } else {
            $_type = $type;
        }

        if (!$_type) {
            throw new Exception("The type can not be set to empty!");
        }

        if (!isset(self::$_instance[$_type])) {
            $conf = Config::get("Log.{$_type}");
            if (!isset($conf)) {
                throw new Exception("The '{$_type}' type log config does not exists!");
            }

            $class = self::getNamespace() . "\\" . ucfirst($_type);
            $obj = new $class();

            if (!$obj instanceof LogDriver) {
                throw new Exception("The '{$class}' not instanceof LogDriver!");
            }

            $obj->init($conf);
            self::$_instance[$_type] = $obj;

        } else {
            $obj = self::$_instance[$_type];
        }

        return $obj;
    }

    public static function write($message, $type=self::INFO)
    {
        return self::instance()->write($message, $type);
    }
}
