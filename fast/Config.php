<?php
declare (strict_types=1);

namespace fast;


use fast\helper\Arr;

class Config
{
    /**
     * 所有的配置文件路径
     * @var array
     */
    protected static array $paths = [];

    /**
     * 所有的配置项
     * @var array
     */
    protected static array $configs = [];

    function __construct()
    {

    }

    public function init()
    {
        self::addPaths("config.php");
        self::addPaths("extra.php");
        self::importConfig(self::$paths);
    }

    /**
     * 设置配置项
     * @param $key
     * @param $value
     */
    public static function set($key, $value): void
    {
        self::$configs[$key] = $value;
    }

    /**
     * 获取配置项
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function get(string $key = null, $default = null)
    {
        if (empty($key)) {
            return self::$configs;
        }

        if (false === strpos($key, '.')) {
            return self::$configs[$key] ?? $default;
        }

        $keys = explode('.', $key);
        $config = self::$configs;

        // 数组拆分
        foreach ($keys as $_key) {
            if (isset($config[$_key])) {
                $config = $config[$_key];
            } else {
                return $default;
            }
        }

        return $config;
    }

    /**
     * 加载配置
     * @param $filename
     */
    private static function addConfig($filename): void
    {
        $configArr = include_once($filename);
        if (is_array($configArr)) {
            self::$configs = Arr::arrayMergeRecursiveUnique(self::$configs, $configArr);
        }
    }

    /**
     * 导入配置
     * @param $paths
     */
    private static function importConfig($paths): void
    {
        foreach ($paths as $path) {
            self::addConfig($path);
        }
    }

    /**
     * 设置配置路径
     * @param $filename
     */
    private static function addPaths($filename): void
    {
        $path = self::getConfigFilePathPrefix();
        $configPath = $path . DIRECTORY_SEPARATOR . $filename;
        self::$paths[] = $configPath;
    }

    /**
     * 获取配置文件直接返回，不保存在当前对象
     * @param $filename
     * @return array
     */
    public static function roughGetByPath($filename): array
    {
        $configArr = include_once($filename);
        return is_array($configArr) ? $configArr : [];
    }

    /**
     * 获取配置路径位置
     * @return string
     */
    public static function getConfigFilePathPrefix(): string
    {
        return APP_PATH . DIRECTORY_SEPARATOR . "config";
    }
}