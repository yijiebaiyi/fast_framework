<?php
declare (strict_types=1);

namespace fast;

use fast\helper\Str;
use fast\http\Http;
use fast\util\Json;

class Exception extends \Exception
{
    /**
     * 是否json输出
     * @var bool
     */
    protected static bool $isJson = false;

    /**
     * http状态码
     * @var int
     */
    protected static int $httpCode = 200;

    /**
     * Exception constructor.
     * @param string $errorMsg 异常信息
     * @param int $code 异常code
     * @param string $file 异常文件
     * @param int $line 异常行数
     */
    public function __construct(string $errorMsg = '', int $code = 0, string $file = '', int $line = 0)
    {
        parent::__construct($errorMsg, $code);
        if (!empty($file)) {
            $this->file = $file;
        }
        if (!empty($line)) {
            $this->line = $line;
        }

        $isDebugging = static::isDebugging();
        if (false === $isDebugging) {
            ini_set("display_errors", "off");
            error_reporting(E_ALL);
        }
    }

    /**
     * 初始化
     */
    public static function init()
    {
        // 注册异常处理函数
        set_exception_handler(array(__CLASS__, 'handler'));
    }

    /**
     * 注册异常处理函数
     * @param $exception
     * @throws Exception
     */
    public static function handler($exception)
    {
        // 设置http状态码，发送header
        if (in_array($exception->getCode(), array_keys(Http::$httpStatus))) {
            self::$httpCode = $exception->getCode();
        } else {
            self::$httpCode = 500;
        }
        Http::sendHeader(self::$httpCode);

        // 异常信息格式化输出
        $echoExceptionString = "<b>message</b>:  {$exception->getMessage()}<br/>" .
            "<b>code</b>:  {$exception->getCode()}<br/>" .
            "<b>file</b>:  {$exception->getFile()}<br/>" .
            "<b>line</b>:  {$exception->getLine()}<br/>";

        $serverVarDump = Str::dump(false, $_SERVER);
        $postVarDump = Str::dump(false, $_POST);
        $filesVarDump = Str::dump(false, $_FILES);
        $cookieVarDump = Str::dump(false, $_COOKIE);

        $logExceptionString = "message:  {$exception->getMessage()}" . PHP_EOL .
            "code:  {$exception->getCode()}" . PHP_EOL .
            "file:  {$exception->getFile()}" . PHP_EOL .
            "line:  {$exception->getLine()}" . PHP_EOL .
            "\$_SERVER:  {$serverVarDump}" . PHP_EOL .
            "\$_POST:  {$postVarDump}" . PHP_EOL .
            "\$_COOKIE:  {$cookieVarDump}" . PHP_EOL .
            "\$_FILES:  {$filesVarDump}";
        Log::write($logExceptionString, Log::ERROR);

        // debug模式将错误输出
        if (static::isDebugging()) {
            if (self::$isJson) {
                echo Json::encode(["message" => $exception->getMessage(), "code" => 0]);
                App::_end();
            } else {
                echo $echoExceptionString;
            }
        }
    }

    /**
     * 返回是否是调试模式
     * @return bool
     */
    public static function isDebugging(): bool
    {
        return Env::get("APP_DEBUG") ? true : false;
    }
}


