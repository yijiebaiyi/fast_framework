<?php
declare (strict_types=1);

namespace fast;


use fast\orm\ConnectionInterface;

class Db
{
    /**
     * 当前单例
     * @var object
     */
    protected static object $instance;

    /**
     * 当前连接器
     * @var object
     */
    protected static object $builder;

    /**
     * 当前连接
     * @var object
     */
    protected static object $connection;

    /**
     * 获取命名空间
     * @return string
     */
    public static function getNamespace(): string
    {
        return "fast\\orm\\driver";
    }

    private function __construct()
    {

    }

    /**
     * 获取当前数据库实例（初始化）
     * @param $selector string 选择器
     * @return object
     * @throws Exception
     */
    public static function getInstance($selector = "master"): object
    {
        if (!empty(static::$builder)) {
            return static::$builder;
        }

        // 获取配置文件
        $configFileName = "db_" . strtolower(Env::get("APP_ENV")) . ".php";
        $configFilepath = Config::getConfigFilePathPrefix() . DIRECTORY_SEPARATOR . $configFileName;
        if (!file_exists($configFilepath)) {
            throw new Exception("database config file does not exist: {$configFileName}");
        }
        $config =  Config::roughGetByPath($configFilepath);

        // 获取数据库驱动
        $driverName = $config["default"] ?? "mysql";
        $className = static::getNamespace() . "\\" . ucwords($driverName);
        if (!class_exists($className)) {
            throw new Exception("database driver does not exist: {$driverName}");
        }
        static::$builder = new $className;
        if (!static::$builder instanceof ConnectionInterface) {
            throw new Exception("database driver not belongs to ConnectionInterface: {$driverName}");
        }

        if (!isset($config[$driverName][$selector])) {
            throw new Exception("database selector does not exist: {$driverName}");
        }

        $dbConfig = $config[$driverName][$selector];
        static::$connection = self::$builder->connect($dbConfig);

        static::$instance = new self();
        return static::$builder;
    }

}