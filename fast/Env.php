<?php

declare (strict_types=1);

namespace fast;


/**
 * Env管理类
 */
class Env
{
    /**
     * 环境变量数据
     * @var array
     */
    protected static array $data = [];

    public function __construct()
    {
        static::$data = $_ENV;
    }

    public function init()
    {
        static::load(APP_PATH . DIRECTORY_SEPARATOR . ".env");
    }

    /**
     * 加载环境变量定义文件
     * @param string $file 环境变量定义文件
     * @return void
     */
    public static function load(string $file): void
    {
        $env = parse_ini_file($file, true) ?: [];
        static::set($env);
    }

    /**
     * 获取环境变量值
     * @param mixed $name 环境变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get(string $name = null, $default = null)
    {
        if (is_null($name)) {
            return static::$data;
        }

        $name = strtoupper(str_replace('.', '_', $name));

        if (isset(static::$data[$name])) {
            return static::$data[$name];
        }

        return static::getEnv($name, $default);
    }

    protected static function getEnv(string $name, $default = null)
    {
        $result = getenv('PHP_' . $name);

        if (false === $result) {
            return $default;
        }

        if ('false' === $result) {
            $result = false;
        } elseif ('true' === $result) {
            $result = true;
        }

        if (!isset(static::$data[$name])) {
            static::$data[$name] = $result;
        }

        return $result;
    }

    /**
     * 设置环境变量值
     * @param string|array $env 环境变量
     * @param mixed $value 值
     * @return void
     */
    public static function set($env, $value = null): void
    {
        if (is_array($env)) {
            $env = array_change_key_case($env, CASE_UPPER);

            foreach ($env as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        static::$data[$key . '_' . strtoupper($k)] = $v;
                    }
                } else {
                    static::$data[$key] = $val;
                }
            }
        } else {
            $name = strtoupper(str_replace('.', '_', $env));

            static::$data[$name] = $value;
        }
    }

    /**
     * 检测是否存在环境变量
     * @param string $name 参数名
     * @return bool
     */
    public function has(string $name): bool
    {
        return !is_null($this->get($name));
    }
}
