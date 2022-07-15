<?php
declare (strict_types = 1);

namespace fast;


class Hook
{
    static array $hookMap = [];

    public function __construct()
    {

    }

    /**
     * 添加标签
     * @param $tag
     * @param $behavior
     */
    public static function add($tag, $behavior)
    {
        !isset(self::$hookMap[$tag]) && self::$hookMap[$tag] = [];
        self::$hookMap[$tag][] = $behavior;
    }

    /**
     * 批量导入标签
     * @param $tags
     */
    public static function import($tags)
    {
        foreach ($tags as $tag => $behaviors) {
            foreach ($behaviors as $behavior) {
                self::add($tag, $behavior);
            }
        }
    }

    /**
     * 触发标签
     * @param $tag
     */
    public static function listen($tag)
    {
        if (!isset(self::$hookMap[$tag])) {
            return;
        }

        foreach (self::$hookMap[$tag] as $behavior) {
            call_user_func($behavior);
        }
    }
}