<?php
declare (strict_types=1);

namespace fast\cache\driver;


use fast\cache\CacheDriver;

class Memcached extends CacheDriver
{
    /**
     * 初始化
     * @param array $conf
     * @return $this
     */
    public function init(array $conf): self
    {
        if (empty($this->_masterObj)) {
            $this->masterObj = new \Memcached;
            if (!empty($conf['options'])) {
                $this->masterObj->setOptions($conf['options']);
            }
            $_servers = $conf['servers'];

            $_hosts = array_column($_servers, 'host');
            $_ports = array_column($_servers, 'port');
            $_weights = array_column($_servers, 'weight');

            if ($_hosts) {
                $_servers = array_map(null, $_hosts, $_ports, $_weights);
            }

            $this->masterObj->addServers($_servers);
            $this->slaveObj = $this->masterObj;
        }
        return $this;
    }

    /**
     * 清空数据
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
     * 删除数据
     * @param mixed $key
     * @return bool
     */
    public function delete($key) :bool
    {
        return $this->masterObj->delete($this->makeKey($key));
    }

    /**
     * 设置值
     * @param string $key
     * @param string $var
     * @param int $expire
     * @return bool
     */
    public function set($key = '', $var = '', $expire = 3600) :bool
    {
        return $this->masterObj->set($this->makeKey($key), $this->makeValue($var, $expire), $expire);
    }

    /**
     * 批量获取数据
     * @param array $keys 由键组成的数组
     * @param bool $checkClear 检查是否被清除
     * @return array
     */
    public function gets(array $keys, $checkClear = true) :array
    {
        $data = array();
        if ($checkClear) {
            foreach ($keys as $_idx => $_key) {
                $data[$_key] = $this->get($_key, $checkClear);
            }
        } else {
            $_keys = array();
            foreach ($keys as $_key) {
                $_keys[$_key] = $this->makeKey($_key);
            }

            $_data = $this->masterObj->getMulti($_keys);

            $__keys = array_flip($_keys);
            foreach ($_data as $_k => $_v) {
                $data[$__keys[$_k]] = isset($_v['value']) ? $_v['value'] : null;
            }
        }

        return $data;
    }

    /**
     * 批量写入数据
     * @param array $items 键值对数组
     * @param int $expire 过期时间
     * @return bool
     */
    public function sets(array $items, $expire = 3600) :bool
    {
        foreach ($items as $_key => $_val) {
            $this->set($_key, $_val, $expire);
        }
        return true;
    }

    /**
     * 不覆盖添加
     * @param string $key
     * @param string $var
     * @param int $expire
     * @return bool
     */
    public function add($key = '', $var = '', $expire = 3600) :bool
    {
        return $this->masterObj->add($this->makeKey($key), $this->makeValue($var, $expire), $expire);
    }
}

