<?php
declare (strict_types=1);

namespace fast\cache\driver;


use fast\cache\CacheDriver;

class Memcache extends CacheDriver
{
    /**
     * 服务配置（集群）
     * @var array
     */
    protected static array $servers = array();

    /**
     * 初始化
     * @param array $conf 配置
     * @return $this
     */
    public function init(array $conf): self
    {
        if (empty($this->_masterObj)) {
            $this->masterObj = new \Memcache;
            if (!empty($conf['servers'])) {
                self::$servers = $conf['servers'];
            }
            foreach (self::$servers as $_server) {
                $this->masterObj->addServer($_server['host'], $_server['port']);
            }
            $this->slaveObj = $this->masterObj;
        }

        return $this;
    }

    /**
     * 清除所有数据
     * @return bool
     */
    public function flush(): bool
    {
        return $this->masterObj->flush();
    }

    /**
     * 获取数据
     * @param array|string $key
     * @param int $time 以引用的方式返回数据添加时间
     * @param int $expire 以引用的方式返回当前值的过期时间
     * @return mixed
     */
    public function get($key, &$time = null, &$expire = null)
    {
        $_key = $this->makeKey($key);
        $res = $this->masterObj->get($_key);

        if ($res && isset($res['value'])) {
            $time = $res['time'];
            $expire = $res['expire'];
            return $res['value'];
        }

        return null;
    }

    /**
     * 批量获取数据
     * @param array $keys 由键组成的数组
     * @return array
     */
    public function gets(array $keys): array
    {
        $data = array();
        foreach ($keys as $_key) {
            $data[$_key] = $this->get($_key);
        }
        return $data;
    }

    /**
     * 删除数据
     * @param mixed $key
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->masterObj->delete($this->makeKey($key));
    }

    /**
     * 设置值
     * @param string $key
     * @param string $var
     * @param int $expire
     * @return mixed
     */
    public function set($key = '', $var = '', $expire = 3600): bool
    {
        return $this->masterObj->set($this->makeKey($key), $this->makeValue($var, $expire), MEMCACHE_COMPRESSED, $expire);
    }

    /**
     * 批量写入数据
     * @param array $items 键值对数组
     * @param int $expire 过期时间
     * @return bool
     */
    public function sets(array $items, $expire = 3600): bool
    {
        foreach ($items as $_key => $_val) {
            $this->set($_key, $_val, $expire);
        }

        return true;
    }

    /**
     * 写入数据，如果存在则不覆盖
     * @param string $key
     * @param string $var
     * @param int $expire
     * @return bool
     */
    public function add($key = '', $var = '', $expire = 3600): bool
    {
        return $this->masterObj->add($this->makeKey($key), $this->makeValue($var, $expire), MEMCACHE_COMPRESSED, $expire);
    }

}

