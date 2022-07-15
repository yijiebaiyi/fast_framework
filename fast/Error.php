<?php
declare (strict_types = 1);

namespace fast;


class Error
{
    public function construct()
    {

    }

    public function init()
    {
        set_error_handler(array(__CLASS__, "handler"));
    }

    /**
     * @param $level
     * @param $errorMsg
     * @param $file
     * @param $line
     * @throws Exception
     */
    public static function handler($level, $errorMsg, $file, $line)
    {
//        register_shutdown_function();
        throw new Exception($errorMsg, intval($level), $file, intval($line));
    }
}