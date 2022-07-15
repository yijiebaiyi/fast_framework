<?php
declare (strict_types=1);

namespace fast;


class Route
{
    /**
     * @var bool 是否初始化
     */
    private bool $isInit = false;

    public function __construct()
    {

    }

    /**
     * 初始化
     * @throws Exception
     */
    public function init()
    {
        if (false === $this->isInit) {
            // 初始化操作

            $this->distribute();
            $this->isInit = true;
        }
    }

    /**
     * 路由分发
     * @throws Exception
     */
    public function distribute()
    {
        // 解析path_info
        if (isset($_SERVER['PATH_INFO'])) {
            $url = explode('/', trim($_SERVER['PATH_INFO'], "/"));
            if (count($url) < 3) {
                $url = array_pad($url, 3, "index");
            }
        } else {
            $url = array_pad([], 3, "index");
        }

        // 获取类名和方法名
        $className = self::formatClassName($url);
        $actionName = self::formatActionName($url);

        if (!class_exists($className)) {
            throw new Exception("the controller is not exist: {$className}", 404);
        }

        $class = new $className();

        if (!is_callable([$class, $actionName])) {
            throw new Exception("the action is not exist: {$className} -> {$actionName}", 404);
        }

        if (!$class instanceof Controller) {
            throw new Exception("the controller not belongs to fast\\Controller: {$className}", 403);
        }

        // 将请求分发
        $class->$actionName();
    }

    /**
     * 获取方法名
     * @param $url array PATH_INFO地址
     * @return string
     */
    private function formatActionName(array $url): string
    {
        return preg_replace_callback('/_([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, strtolower($url[2]));
    }

    /**
     * 获取类名
     * @param array $url PATH_INFO地址
     * @return string
     */
    private function formatClassName(array $url): string
    {
        // 下划线转驼峰
        $className = preg_replace_callback('/_([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, strtolower($url[1]));
        return "\\app\\" . strtolower($url[0]) . "\\controller\\" . ucwords($className) . "Controller";
    }
}