<?php
declare (strict_types=1);

namespace fast\orm;


interface ConnectionInterface
{
    /**
     * 连接
     * @param array $config
     * @return mixed
     */
    public function connect(array $config);

    /**
     * 关闭连接
     * @return mixed
     */
    public function close();

    /**
     * 更新
     * @param $data
     * @return mixed
     */
    public function update($data);

    /**
     * 查询
     * @return mixed
     */
    public function select();

    /**
     * 删除
     * @return mixed
     */
    public function delete();

    /**
     * 新增
     * @param $data
     * @return mixed
     */
    public function insert($data);

    /**
     * 原生查询
     * @param string $sql
     * @return mixed
     */
    public function query(string $sql);

    /**
     * 获取最后一条sql
     * @return mixed
     */
    public function getLastSql();

    /**
     * 开启事务
     * @return mixed
     */
    public function begin();

    /**
     * 事务回滚
     * @return mixed
     */
    public function rollback();

    /**
     * 事务提交
     * @return mixed
     */
    public function commit();

}
