<?php

namespace fast\orm\driver;


use fast\Exception;
use fast\orm\ConnectionInterface;
use mysqli;
use mysqli_result;

class Mysql implements ConnectionInterface
{
    /**
     * @var string
     */
    protected string $_host = "127.0.0.1";

    /**
     * @var string
     */
    protected string $_user = "root";

    /**
     * @var string
     */
    protected string $_passwd = "";

    /**
     * @var string
     */
    protected string $_dbname = "";

    /**
     * @var int
     */
    protected int $_port = 3306;

    /**
     * @var array
     */
    protected array $_options = [];

    /**
     * 当前连接
     * @var mysqli
     */
    protected mysqli $_connection;

    /**
     * 当前连接错误
     * @var string
     */
    protected string $_error = "";

    /**
     * 当前sql
     * @var string
     */
    protected string $_sql;

    /**
     * 当前表
     * @var string
     */
    protected string $_table;

    /**
     * 当前条件
     * @var string
     */
    protected string $_where = "";

    /**
     * 当前字段
     * @var string
     */
    protected string $_fields = "";

    /**
     * 当前偏移量
     * @var int
     */
    protected int $_offset = 0;

    /**
     * 当前limit
     * @var int
     */
    protected int $_limit = 0;

    /**
     * 当前排序
     * @var string
     */
    protected string $_order = "";

    /**
     * 当前group
     * @var string
     */
    protected string $_group = "";

    /**
     * 比较操作符
     * @var array|string[]
     */
    private static array $compareOperator = array(' NOT BETWEEN', ' BETWEEN', ' NOT IN', ' IN', ' NOT LIKE',
        ' IS NOT', ' LIKE', ' IS', ' NOT REGEXP', ' REGEXP', ' RLIKE',
        '>=', '<=', '<>', '!=', '>', '<', '=',
    );

    public function connect($config = [])
    {
        if (!empty($this->_connection)) {
            return $this;
        }

        foreach (["host", "user", "passwd", "dbname", "port", "options"] as $attribute) {
            $selfAttribute = "_" . $attribute;
            isset($config[$attribute]) && $this->{$selfAttribute} = $config[$attribute];
        }

        $connection = mysqli_connect($this->_host, $this->_user, $this->_passwd, $this->_dbname, (int)$this->_port);
        if (!$connection) {
            $this->_error = mysqli_connect_error();
            return false;
        }

        $this->_connection = $connection;
        return $this;
    }

    /**
     * 返回当前连接
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return $this->_connection;
    }

    /**
     * 设置表名
     * @param $tableName
     * @return $this
     */
    public function table($tableName): self
    {
        $this->_table = $tableName;
        return $this;
    }

    /**
     * 设置条件
     * 后期优化：where的置空，单个对象where的多次调用
     * @param $where
     * @return $this
     */
    public function where($where): self
    {
        $this->_where = $this->parseWhere($where);
        return $this;
    }

    /**
     * 排序
     * 后期优化：order支持数组传值排序
     * @param string $order
     * @return $this
     */
    public function order(string $order): self
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * 分组
     * @param string $group
     * @return $this
     */
    public function group(string $group): self
    {
        $this->_group = $group;
        return $this;
    }

    /**
     * 偏移量
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): self
    {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * limit
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): self
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * 字段
     * @param string $fields
     * @return $this
     */
    public function fields(string $fields): self
    {
        $this->_fields = $fields;
        return $this;
    }

    /**
     * 查询
     * @return array|false|mixed
     * @throws Exception
     */
    public function select()
    {
        $this->checkMysqlOperate("table_empty");
        empty($this->_fields) && $this->_fields = "*";

        $sql = "SELECT {$this->_fields} FROM {$this->_table}";
        !empty($this->_where) && $sql .= " WHERE {$this->_where}";
        !empty($this->_order) && $sql .= " ORDER BY {$this->_order}";
        !empty($this->_group) && $sql .= " GROUP BY {$this->_group}";
        !empty($this->_limit) && $sql .= " LIMIT {$this->_offset}, {$this->_limit}";

        $this->_sql = $sql;
        $mysqliResult = mysqli_query($this->_connection, $this->_sql);
        if (false === $mysqliResult) {
            $this->_error = mysqli_error($this->_connection);
            return false;
        }
        return mysqli_fetch_all($mysqliResult, MYSQLI_ASSOC);
    }

    /**
     * 更新单条数据
     * @param $data
     * @return bool|mixed|mysqli_result
     * @throws Exception
     */
    public function update($data)
    {
        $this->checkMysqlOperate("table_empty");
        $this->checkMysqlOperate("where_empty");

        $sets = [];
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                is_string($value) && $value = "'{$value}'";
                array_push($sets, "$key=$value");
            }
        } else {
            throw new Exception("input data should be a array");
        }
        $setMessages = implode(',', $sets);
        $this->_sql = 'UPDATE ' . $this->_table . ' SET ' . $setMessages . ' WHERE ' . $this->_where;
        $mysqliResult = mysqli_query($this->_connection, $this->_sql);
        if (false === $mysqliResult) {
            $this->_error = mysqli_error($this->_connection);
            return false;
        }
        return $mysqliResult;
    }

    /**
     * 删除
     * @return bool|mixed|mysqli_result
     * @throws Exception
     */
    public function delete()
    {
        $this->checkMysqlOperate("table_empty");
        $this->checkMysqlOperate("where_empty");

        $this->_sql = 'DELETE FROM ' . $this->_table . ' WHERE ' . $this->_where;
        $mysqliResult = mysqli_query($this->_connection, $this->_sql);
        if (false === $mysqliResult) {
            $this->_error = mysqli_error($this->_connection);
            return false;
        }
        return $mysqliResult;
    }

    /**
     * 插入单条数据
     * @param $data
     * @return bool
     * @throws Exception
     */
    public function insert($data): bool
    {
        $this->checkMysqlOperate("table_empty");
        $this->checkMysqlOperate("data_empty", $data);
        if (!is_array($data)) {
            throw new Exception("input data should be a array");
        }

        $fields = '(' . implode(',', array_keys($data)) . ')';
        $values = '(' . implode(',', array_map(
                function ($v) {
                    is_string($v) && $v = "'" . $v . "'";
                    return $v;
                }, array_values($data)
            )) . ')';

        $this->_sql = 'INSERT INTO ' . $this->_table . $fields . ' VALUES ' . $values;
        $mysqliResult = mysqli_query($this->_connection, $this->_sql);
        if (false === $mysqliResult) {
            $this->_error = mysqli_error($this->_connection);
            return false;
        }
        return $mysqliResult;
    }

    /**
     * 原生查询
     * @param string $sql
     * @return mixed
     */
    public function query(string $sql)
    {
        $this->_sql = $sql;
        $mysqliResult = mysqli_query($this->_connection, $this->_sql);
        if (false === $mysqliResult) {
            $this->_error = mysqli_error($this->_connection);
            return false;
        }

        if (substr($this->_sql, 0, 6) == "SELECT") {
            return mysqli_fetch_all($mysqliResult, MYSQLI_ASSOC);
        }
        return $mysqliResult;
    }

    /**
     * 获取最后一条sql
     * @return string
     */
    public function getLastSql(): string
    {
        return $this->_sql;
    }

    /**
     * 开启事务
     * @return bool
     */
    public function begin(): bool
    {
        return mysqli_begin_transaction($this->_connection);
    }

    /**
     * 事务回滚
     * @return bool
     */
    public function rollback(): bool
    {
        return mysqli_rollback($this->_connection);
    }

    /**
     * 事务提交
     * @return bool
     */
    public function commit(): bool
    {
        return mysqli_commit($this->_connection);
    }

    /**
     * 解析 where
     * @param $where
     * @param $logic
     * @return string
     */
    public function parseWhere($where, $logic = "AND"): string
    {
        if (empty($where)) {
            return '';
        }

        if (is_string($where)) {
            return $where;
        }

        if (!is_array($where)) {
            return '';
        }

        $_where = [];
        foreach ($where as $_field => $_val) {
            $_field = trim($_field);
            $_comparison = '=';
            foreach (self::$compareOperator as $_cval) {
                $_cOffset = strripos($_field, $_cval);
                if ($_cOffset > 0) {//找到对应的运算符，
                    $_field = trim(substr($_field, 0, $_cOffset));
                    $_comparison = trim($_cval);
                    break;
                }
            }

            $_field = self::formatField($_field);

            if (!empty($_val) && is_array($_val)) {
                $_comparison = $_comparison == '=' ? 'IN' : $_comparison;//替换默认的=号为IN查询

                if ($_comparison == 'BETWEEN' || $_comparison == 'NOT BETWEEN') {
                    $_where[] = "({$_field} {$_comparison} " . join(' AND ', $_val) . ")";
                } else {
                    $_where[] = "({$_field} {$_comparison} (" . join(',', $_val) . "))";
                }
            } else if (is_string($_val)) {
                $_where[] = "({$_field} {$_comparison} {$_val})";
            }
        }
        $where = join(" {$logic} ", $_where);
        return $where ? "({$where})" : '';
    }

    /**
     * 格式化字段
     * @param string $field 字段名
     * @return string
     */
    public static function formatField(string $field): string
    {
        if (strpos($field, '.')) {
            $field = str_replace('.', '`.`', $field);
        }

        $field = '`' . $field . '`';
        return $field;

    }

    /**
     * 获取sql error
     * @return string
     */
    public function getError(): string
    {
        return $this->_error;
    }

    /**
     * 非法获取
     * @param $attribute
     * @throws Exception
     */
    public function __get($attribute)
    {
        throw new Exception("database config attribute is not exist:" . $attribute);
    }

    /**
     * 非法设置
     * @param $attribute
     * @param $attributeValue
     * @throws Exception
     */
    public function __set($attribute, $attributeValue)
    {
        throw new Exception("database config attribute is not exist:" . $attribute);
    }

    /**
     * 关闭连接
     * @return bool
     */
    public function close(): bool
    {
        return mysqli_close($this->_connection);
    }

    /**
     * 异常抛出
     * @param string $type
     * @param $value
     * @throws Exception
     */
    public function checkMysqlOperate(string $type, $value = "")
    {
        switch ($type) {
            case "table_empty":
                if (empty($this->_table)) {
                    throw new Exception("be operated table cannot be empty");
                }
                break;
            case "where_empty":
                if (empty($this->_where)) {
                    throw new Exception("update condition cannot be empty");
                }
                break;
            case "data_empty":
                if (empty($value)) {
                    throw new Exception("input data cannot be empty");
                }
                break;
        }
    }
}