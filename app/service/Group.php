<?php
/**
 * Created by: tjx
 * Date: 2021-08-31
 */

namespace app\service;


class Group
{
    public static $a = 0;

    function __construct($a =1)
    {
        static::$a = $a;
    }

    public function getA()
    {
        echo self::$a;
    }
}