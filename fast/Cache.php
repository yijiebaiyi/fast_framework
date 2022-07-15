<?php
declare (strict_types=1);

namespace fast;

use fast\cache\CacheDriver;

class Cache
{
    /**
     * @var CacheDriver[] ;
     */
    protected static array $_instance = [];

    /**
     * 获取命名空间
     * @return string
     */
    public static function getNamespace(): string
    {
        return "fast\\cache\\driver";
    }

    /**
     * 单例
     * @param null $type
     * @return CacheDriver
     * @throws Exception
     */
    public static function instance($type = "default"): CacheDriver
    {
        if ($type === "default") {
            $_type = Config::get("Cache.default");
        } else {
            $_type = $type;
        }

        if (!$_type) {
            throw new Exception("The type can not be set to empty!");
        }

        if (!isset(self::$_instance[$_type])) {
            $conf = Config::get("Cache.{$_type}");

            if (empty($conf)) {
                throw new Exception("The '{$_type}' type cache config does not exists!");
            }

            $class = self::getNamespace() . "\\" . ucfirst($_type);
            $obj = new $class();

            if (!$obj instanceof CacheDriver) {
                throw new Exception("The '{$class}' not instanceof CacheDriver!");
            }

            $obj->init($conf);
            self::$_instance[$_type] = $obj;

        } else {
            $obj = self::$_instance[$_type];
        }

        return $obj;
    }
}