<?php
declare (strict_types=1);

namespace fast\log;


abstract class LogDriver
{
    /**
     * 初始化
     * @param array $conf
     * @return $this
     */
    abstract public function init(array $conf = []): self;

    /**
     * 日志写入
     * @param string $message 写入内容
     * @param string $type 日志类型
     * @return mixed
     */
    abstract public function write(string $message, string $type);
}
