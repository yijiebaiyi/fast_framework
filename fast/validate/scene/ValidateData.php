<?php

namespace fast\validate\scene;


use fast\validate\ValidateInterface;

class ValidateData implements ValidateInterface
{
    /**
     * 所有的校验错误信息
     * @var array
     */
    protected array $errors = [];

    /**
     * 正则验证规则
     * @var array
     */
    protected array $pregCheckRules = [
        'alpha' => '/^[A-Za-z]+$/',
        'alphaNum' => '/^[A-Za-z0-9]+$/',
        'alphaDash' => '/^[A-Za-z0-9\-\_]+$/',
        'chs' => '/^[\x{4e00}-\x{9fa5}\x{9fa6}-\x{9fef}\x{3400}-\x{4db5}\x{20000}-\x{2ebe0}]+$/u',
        'chsAlpha' => '/^[\x{4e00}-\x{9fa5}\x{9fa6}-\x{9fef}\x{3400}-\x{4db5}\x{20000}-\x{2ebe0}a-zA-Z]+$/u',
        'chsAlphaNum' => '/^[\x{4e00}-\x{9fa5}\x{9fa6}-\x{9fef}\x{3400}-\x{4db5}\x{20000}-\x{2ebe0}a-zA-Z0-9]+$/u',
        'chsDash' => '/^[\x{4e00}-\x{9fa5}\x{9fa6}-\x{9fef}\x{3400}-\x{4db5}\x{20000}-\x{2ebe0}a-zA-Z0-9\_\-]+$/u',
        'mobile' => '/^1[3-9]\d{9}$/',
        'idCard' => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
        'zip' => '/\d{6}/',
    ];

    /**
     * 数字相关的规则名称
     * @var array|string[]
     */
    protected array $operatorCheckName = [
        "eq", // 等于
        "neq", // 不等于
        "lt", // 小于
        "gt", // 大于
        "elt", // 小于等于
        "egt", // 大于等于
        "between", // 在...之间，示例：(1, 3), [1, 3], (1, 3] 代表大于1，小于等于3
        "notBetween", // 不在...之间
    ];

    /**
     * 数据类型的规则名称
     * @var array|string[]
     */
    protected array $typeCheckName = [
        "number", // 是数组
        "int", // 是整数
        "float", // 是浮点数
        "bool", // 是布尔值
        "array", // 是数组
        "date", // 是日期格式
    ];

    /**
     * string相关校验
     * @var array|string[]
     */
    protected array $stringCheckName = [
        "in", // 在...中
        "notIn", // 不在...中
        "max", // 最大长度
        "min", // 最小长度
        "length", // 长度
    ];

    /**
     * 返回所有的错误
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 验证数据
     * @param array $data
     * @param array $rules
     * @return $this
     */
    public function check(array $data, array $rules): self
    {
        foreach ($rules as $rule => $message) {
            $dataRule = explode(".", $rule);
            if (count($dataRule) < 2) {
                continue;
            }

            // 必传校验
            if ($dataRule[1] == "required" && !isset($data[$dataRule[0]])) {
                array_push($this->errors, $message);
                continue;
            }

            if (!isset($data[$dataRule[0]])) {
                continue;
            }

            // 类型校验
            if (in_array($dataRule[1], $this->typeCheckName)) {
                if (false === self::typeCheck(strval($dataRule[1]), $data[$dataRule[0]])) {
                    array_push($this->errors, $message);
                    continue;
                }
            }

            // 字符校验
            if (in_array($dataRule[1], $this->stringCheckName) && isset($dataRule[2])) {
                if (false === self::stringCheck(strval($dataRule[1]), $dataRule[2], $data[$dataRule[0]])) {
                    array_push($this->errors, $message);
                    continue;
                }
            }

            // 数字校验
            if (in_array($dataRule[1], $this->operatorCheckName) && isset($dataRule[2])) {
                if (false === self::operatorCheck(strval($dataRule[1]), $dataRule[2], $data[$dataRule[0]])) {
                    array_push($this->errors, $message);
                    continue;
                }
            }

            // 正则校验
            if (in_array($dataRule[1], array_keys($this->pregCheckRules))) {
                if (false === self::pregCheck(strval($dataRule[1]), $data[$dataRule[0]])) {
                    array_push($this->errors, $message);
                    continue;
                }
            }
        }
        return $this;
    }

    /**
     * 设置正则规则
     * @param string $name
     * @param string $preg
     */
    public function addPregRule(string $name, string $preg): void
    {
        $this->pregCheckRules[$name] = $preg;
    }

    /**
     * 运算符规则校验
     * @param $rule string 规则
     * @param $value mixed 规则值
     * @param $dataValue mixed 待验证数据
     * @return bool
     */
    public static function operatorCheck(string $rule, $value, $dataValue): bool
    {
        $flag = true;
        switch ($rule) {
            case "eq":
                $dataValue != $value && $flag = false;
                break;
            case "neq":
                $dataValue == $value && $flag = false;
                break;
            case "lt":
                $dataValue >= $value && $flag = false;
                break;
            case "gt":
                $dataValue <= $value && $flag = false;
                break;
            case "elt":
                $dataValue > $value && $flag = false;
                break;
            case "egt":
                $dataValue < $value && $flag = false;
                break;
            case "between":
                $value = explode(",", $value);
                if (count($value) < 2) {
                    break;
                }
                if (false !== strpos($value[0], "[")) {
                    $dataValue < trim($value[0], "[") && $flag = false;
                    break;
                }
                if (false !== strpos($value[0], "(")) {
                    $dataValue <= trim($value[0], "(") && $flag = false;
                    break;
                }
                if (false !== strpos($value[1], "]")) {
                    $dataValue > trim($value[1], "]") && $flag = false;
                    break;
                }
                if (false !== strpos($value[1], ")")) {
                    $dataValue >= trim($value[1], ")") && $flag = false;
                }
                break;
            case "notBetween":
                $value = explode(",", $value);
                if (count($value) < 2) {
                    break;
                }
                if (false !== strpos($value[0], "[")) {
                    $dataValue >= trim($value[0], "[") && $flag = false;
                    break;
                }
                if (false !== strpos($value[0], "(")) {
                    $dataValue > trim($value[0], "(") && $flag = false;
                    break;
                }
                if (false !== strpos($value[1], "]")) {
                    $dataValue <= trim($value[1], "]") && $flag = false;
                    break;
                }
                if (false !== strpos($value[1], ")")) {
                    $dataValue < trim($value[1], ")") && $flag = false;
                }
                break;
        }
        return $flag;
    }

    /**
     * 运算符规则校验
     * @param $rule string 规则
     * @param $dataValue mixed 待验证数据
     * @return bool
     */
    public static function typeCheck(string $rule, $dataValue): bool
    {
        $flag = true;
        switch ($rule) {
            case "number":
                !is_numeric($dataValue) && $flag = false;
                break;
            case "int":
                !is_int($dataValue) && $flag = false;
                break;
            case "float":
                !is_float($dataValue) && $flag = false;
                break;
            case "bool":
                !is_bool($dataValue) && $flag = false;
                break;
            case "array":
                !is_array($dataValue) && $flag = false;
                break;
            case "date":
                !strtotime($dataValue) && $flag = false;
                break;
        }
        return $flag;
    }

    /**
     * 运算符规则校验
     * @param $rule string 规则
     * @param $dataValue mixed 待验证数据
     * @return bool
     */
    public function pregCheck(string $rule, $dataValue): bool
    {
        if (!isset($this->pregCheckRules[$rule])) {
            return false;
        }
        return preg_match($this->pregCheckRules[$rule], $dataValue) ? true : false;
    }

    /**
     * 运算符规则校验
     * @param $rule string 规则
     * @param $value mixed 规则值
     * @param $dataValue mixed 待验证数据
     * @return bool
     */
    public function stringCheck(string $rule, $value, $dataValue): bool
    {
        $flag = true;
        switch ($rule) {
            case "max":
                strlen($dataValue) > $value && $flag = false;
                break;
            case "min":
                strlen($dataValue) < $value && $flag = false;
                break;
            case "length":
                strlen($dataValue) != $value && $flag = false;
                break;
            case "in":
                $value = explode(",", $value);
                !in_array($dataValue, $value) && $flag = false;
                break;
            case "notIn":
                $value = explode(",", $value);
                in_array($dataValue, $value) && $flag = false;
                break;
        }
        return $flag;
    }
}