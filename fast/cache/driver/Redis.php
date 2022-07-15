<?php
declare (strict_types=1);

namespace fast\cache\driver;


use fast\cache\CacheDriver;
use fast\Exception;

class Redis extends CacheDriver
{
    /**
     * 初始化
     *
     * @param $conf
     * @return $this
     * @throws Exception
     */
    public function init($conf): self
    {
        if (empty($conf['master'])) {
            throw new Exception('config is empty!');
        }

        if (empty($this->masterObj) || empty($this->slaveObj)) {
            $_master = $conf['master'];
            $_masterObj = self::_createConn($_master);

            if (!empty($conf['slaves'])) {
                $_slave = count($conf['slaves']) > 1 ? array_rand($conf['slaves']) : reset($conf['slaves']);
                $_slaveObj = self::_createConn($_slave);
            } else {
                $_slaveObj = $_masterObj;
            }

            $this->masterObj = $_masterObj;
            $this->slaveObj = $_slaveObj;
        }

        if (!empty($conf['options'])) {
            $this->_setOptions($conf['options']);
        }

        return $this;
    }

    /**
     * 清除所有数据
     * @return bool
     */
    public function flush(): bool
    {
        return $this->masterObj->flushDB();
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
        $res = $this->slaveObj->get($_key);
        if (is_null($res) || false === $res) {
            return null;
        }

        $res = unserialize($res);
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
     * @param bool $checkClear 检查是否被清除
     * @return array
     */
    public function gets(array $keys, $checkClear = true): array
    {
        $data = array();
        if ($checkClear) {
            foreach ($keys as $_key) {
                $data[$_key] = $this->get($_key, $checkClear);
            }
        } else {
            $_keys = array();
            foreach ($keys as $_key) {
                $_keys[] = $this->makeKey($_key);
            }

            $_data = $this->slaveObj->mget($_keys);

            $_i = 0;
            foreach ($_data as $_v) {
                $data[$keys[$_i]] = isset($_v['value']) ? $_v['value'] : null;
                $_i++;
            }
        }

        return $data;
    }

    /**
     * 写入数据
     * @param mixed $key
     * @param null $value
     * @param int $expire
     * @return bool
     */
    public function set($key, $value = null, $expire = 3600): bool
    {
        return $this->masterObj->set($this->makeKey($key), serialize($this->makeValue($value, $expire)), $expire);
    }

    /**
     * 批量写入数据
     * @param array $items 键值对数组
     * @param int $expire 过期时间
     * @return mixed
     */
    public function sets(array $items, $expire = 3600): bool
    {
        foreach ($items as $_key => $_val) {
            $this->set($_key, $_val, $expire);
        }
        return true;
    }

    /**
     * 删除数据
     * @param $key
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->masterObj->delete($this->makeKey($key));
    }

    /**
     * 创建redis对象
     * @param array $conf 配置 {host => string, port => int, timeout => float}
     * @return \Redis
     */
    protected static function _createConn(array $conf): \Redis
    {
        $_obj = new \Redis();

        $_connectFunc = empty($conf['pconnect']) ? 'connect' : 'pconnect';
        $_obj->$_connectFunc($conf['host'], $conf['port'], $conf['timeout']);
        return $_obj;
    }

    /**
     * 设置option
     * @param array $options
     */
    protected function _setOptions(array $options)
    {
        foreach ($options as $_key => $_val) {
            $this->masterObj->setOption($_key, $_val);
            $this->slaveObj->setOption($_key, $_val);
        }
    }
}