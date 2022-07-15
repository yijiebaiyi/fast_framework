<?php
declare (strict_types=1);

namespace fast\helper;


class Str
{
    /**
     * 格式化var_dump
     * @param mixed $vars 要输出的变量
     * @param bool $isEcho 是否直接输出
     * @return string | void
     */
    public static function dump($isEcho = true, ...$vars)
    {
        ob_start();
        var_dump(...$vars);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (!$isEcho) {
            return $output;
        }

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
        }

        echo $output;
    }
}