<?php
declare (strict_types=1);

namespace fast;


class App
{
    /**
     * @var bool 是否初始化
     */
    private bool $isInit = false;

    public function __construct()
    {

    }

    /**
     * 校验运行环境
     */
    public function validateEnv()
    {
        if (version_compare('7.4.3', PHP_VERSION, '>')) {
            trigger_error("php版本必须大于等于7.4.3，当前版本：" . PHP_VERSION);
        }
    }

    /**
     * 系统初始化运行
     */
    public function init()
    {
        if (false === $this->isInit) {
            define("DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]);
            define("ROOT_PATH", $_SERVER["DOCUMENT_ROOT"]);
            define("RUNTIME_PATH", $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "runtime");
            define("APP_PATH", $_SERVER["DOCUMENT_ROOT"]);

            // 注册自动加载
            require_once FAST_PATH . DIRECTORY_SEPARATOR . "Autoload.php";
            (new Autoload())->init();

            // 注册配置
            (new Config())->init();

            // 加载env
            (new Env())->init();

            // 注册错误和异常
            (new Exception())->init();
            (new Error())->init();
            (new Shutdown())->init();

            // 检验运行环境
            $this->validateEnv();

            // 注册路由
            (new Route())->init();

            $this->isInit = true;
        }
    }

    /**
     * 结束运行
     */
    public static function _end()
    {
        exit();
    }
}