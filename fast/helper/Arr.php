<?php
declare (strict_types = 1);

namespace fast\helper;


class Arr
{
    /**
     * 递归合并两个数组，键唯一
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public static function arrayMergeRecursiveUnique(array $arr1, array $arr2): array
    {
        foreach ($arr2 as $_k => &$_v) {
            if (is_array($_v) && isset($arr1[$_k]) && is_array($arr1[$_k])) {

                $arr1[$_k] = self::arrayMergeRecursiveUnique($arr1[$_k], $_v);
            } else {
                $arr1[$_k] = $_v;
            }
        }
        return $arr1;
    }

    /**
     * 以基本数组合并,多余字段丢弃
     * @param array $baseArr
     * @param array $otherArr
     * @return array;
     */
    public static function arrayMergeBase(array $baseArr, array $otherArr) :array
    {
        foreach ($baseArr as $_key => &$_val) {
            if (isset($otherArr[$_key])) {
                $_val = $otherArr[$_key];
            }
        }
        return $baseArr;
    }

}