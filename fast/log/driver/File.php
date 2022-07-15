<?php
declare (strict_types=1);

namespace fast\log\driver;


use fast\log\LogDriver;

class File extends LogDriver
{
    /**
     * 初始化
     * @param array $conf
     * @return LogDriver
     */
    public function init(array $conf = []): LogDriver
    {
        // 初始化操作
        // .......

        return $this;
    }

    /**
     * 日志写入
     * @param string $message 写入信息
     * @param string $type 日志类型
     * @return false|int
     */
    public function write(string $message, string $type)
    {
        if (empty($message)) {
            trigger_error('$message dose not empty! ');

            return false;
        }

        if (empty($type)) {
            trigger_error('$type dose not empty! ');

            return false;
        }

        $path = APP_PATH . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $type . '/' . date('Ym/d') . '.log';

        $mark = "\n\n===========================================================================\n";
        $mark .= 'time:' . date('Y/m/d H:i:s') . "\n";

        return \fast\util\File::write($mark . $message, $path, (FILE_APPEND | LOCK_EX));
    }
}

