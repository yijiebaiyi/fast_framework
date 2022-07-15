<?php
declare (strict_types=1);

namespace fast\cache;


abstract class CacheDriver
{

    /**
     * 主服务器
     * @var object
     */
    protected object $masterObj;

    /**
     * 从服务器集群
     * @var object
     */
    protected object $slaveObj;

    /**
     * 模块名
     * @var string
     */
    protected string $moduleName = '';

    /**
     * 初始化
     * @param array $conf
     * @return $this
     */
    abstract public function init(array $conf): self;

    /**
     * 获取数据
     * @param array|string $key
     * @param int $time 以引用的方式返回数据添加时间
     * @param int $expire 以引用的方式返回当前值的过期时间
     * @return mixed
     */
    abstract public function get($key, &$time = null, &$expire = null);

    /**
     * 批量获取数据
     * @param array $keys 由键组成的数组
     * @return array
     */
    abstract public function gets(array $keys): array;

    /**
     * 写入数据
     * @param mixed $key 键
     * @param mixed $value 值
     * @param int $expire 生效时间
     * @return mixed
     */
    abstract public function set($key, $value = null, $expire = 3600) :bool;

    /**
     * 批量写入数据
     * @param array $items 键值对数组
     * @param int $expire 过期时间
     * @return mixed
     */
    abstract public function sets(array $items, $expire = 3600) :bool;

    /**
     * 删除数据
     * @param mixed $key 键
     * @return bool
     */
    abstract public function delete($key) :bool;

    /**
     * 删除所有缓存
     * @return bool
     */
    abstract public function flush(): bool;

    /**
     * 组装key
     * @param mixed $param 原始键
     * @return string
     */
    public function makeKey($param = null): string
    {
        if (empty($param)) {
            return '';
        }

        if (is_array($param)) {
            ksort($param);
            $param = http_build_query($param);
        }
        return md5($this->getModuleName() . $param);
    }

    /**
     * 组装value
     * @param mixed $val
     * @param int $expire
     * @return array
     */
    final protected function makeValue($val, $expire = 3600): array
    {
        $val = array(
            'value' => $val,
            'expire' => $expire,
            'time' => time(),
        );
        return $val;
    }

    /**
     * 设置模块名
     * @param string $moduleName
     * @return $this
     */
    public function setModuleName($moduleName = ''): self
    {
        $this->moduleName = $moduleName;
        return $this;
    }

    /**
     * 获取模块名
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }
}
