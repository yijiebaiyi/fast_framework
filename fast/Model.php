<?php
declare (strict_types=1);

namespace fast;


abstract class Model
{
    public array $data = [];

    public string $table = '';

    public static $dbInstance = null;

    public static $dataObj = null;

    public function __construct()
    {
        self::init();
    }

    public function init()
    {
        if (is_null(self::$dbInstance)) {
            self::$dbInstance = Db::getInstance();
        }

        $class =  __CLASS__;
        if (substr($class, 0, -5) != "Model") {
            throw new Exception("the model name is not be allowed:" . $class);
        }

        $this->table = $this->toUnderline(substr($class, -5));
    }

    /**
     * 获取命名空间
     * @return string
     */
    public static function getNamespace(): string
    {
        return "fast\\orm\\driver";
    }

    public function __get(string $key)
    {
        throw new Exception("attribute is not exist:" . $key);
    }

    public function __set(string $key, $value)
    {
        array_push($this->data, [$key, $value]);
    }

    public function insert($data)
    {
        $this->data = $data;
        return self::$dbInstance->table($this->table)->insert($this->data);
    }

    public function update($data)
    {
        $where = [];
        return self::$dbInstance->where($where)->update($data);
    }

    public function toUnderline($str) :string
    {
        $pregRes = preg_replace_callback('/([A-Z]+)/',function($matches)
        {
            return '_'.strtolower($matches[0]);
        },$str);
        return trim(preg_replace('/_{2,}/','_',$pregRes),'_');
    }
}