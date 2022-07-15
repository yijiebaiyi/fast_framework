<?php
declare (strict_types = 1);

namespace fast;


use Exception;

class Autoload
{
    /**
     * @var bool 是否初始化
     */
    private bool $isInit = false;

    public function __construct()
    {

    }

    public function init()
    {
        if (false === $this->isInit) {
            spl_autoload_register(array($this, 'autoload'));

            $this->isInit = true;
        }
    }

    /**
     * @var array 类加载次
     */
    private static array $loadedClassNum = [];

    /**
     * 自动加载
     * @param $name
     * @throws Exception
     */
    public static function autoload($name): void
    {
        if (trim($name) == '') {
            throw new Exception("No class for loading");
        }

        $file = self::formatClassName($name);

        if (isset(self::$loadedClassNum[$file])) {
            self::$loadedClassNum[$file]++;
            return;
        }
        if (!$file || !is_file($file)) {
            return;
        }
        // 导入文件
        include $file;

        if (empty(self::$loadedClassNum[$file])) {
            self::$loadedClassNum[$file] = 1;
        }
    }

    /**
     * 返回全路径
     * @param $className
     * @return string
     */
    private static function formatClassName($className): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $className . '.php';
    }
}