<?php

namespace fast\validate;


interface ValidateInterface
{
    /**
     * 获取所有错误信息
     * @return array
     */
    public function getErrors(): array;

    /**
     * 验证
     * @param array $data
     * @param array $rules
     */
    public function check(array $data, array $rules);
}