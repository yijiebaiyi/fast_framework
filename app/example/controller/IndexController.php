<?php

/**
 * Created by: tjx
 * Date: 2021-07-26
 */

namespace app\example\controller;

use fast\Cache;
use fast\Config;
use fast\Container;
use fast\Controller;
use fast\Exception;
use fast\Hook;
use fast\Log;
use fast\Db;
use fast\validate\scene\ValidateData;

class IndexController extends Controller
{

    /**
     * 测试请求
     * @route /index/index/index?id=1&name=张三
     */
    public function testRequest()
    {
        var_dump($this->request->get("id"), $this->request->get("name"));
    }

    /**
     * 测试log
     * @route /example/index/test_log
     */
    public function testLog()
    {
        Log::write("这是一条info类型的log", Log::INFO);
    }

    /**
     * 测试cache
     * @route /example/index/test_cache_set
     * @throws Exception
     */
    public function testCacheSet()
    {
        $cacheObj = Cache::instance('redis');
        $setRes = $cacheObj->setModuleName("user")->set(["id" => 1], ["name" => "ZhangSan"], 1000);
        if ($setRes) {
            echo "设置成功";
        } else {
            echo "设置失败";
        }
    }

    /**
     * 测试cache
     * @route /example/index/test_cache_get
     * @throws Exception
     */
    public function testCacheGet()
    {
        $cacheObj = Cache::instance('redis');
        $res = $cacheObj->setModuleName("user")->get(["id" => 1], $time, $expire);
        var_dump($res, $time, $expire);
        die;
    }

    /**
     * 测试hook的触发
     * 注：hook类可以单独编写
     * @route /example/index/test_hook
     */
    public function testHook()
    {
        Hook::add("test_hook", [__CLASS__, "hook2"]);
        Hook::add("test_hook", [__CLASS__, "hook1"]);
        Hook::listen("test_hook");
    }

    /**
     * hook1
     */
    public static function hook1()
    {
        echo "这是hook1" . "<br/>";
    }

    public function testAdd()
    {
        echo '<span> 你好啊<span/>';
    }

    public function testAdd2()
    {
        echo '<span> 你好啊<span/>';
    }
    /**
     * hook2
     */
    public static function hook2()
    {
        echo "这是hook2" . "<br/>";
    }

    /**
     * 测试验证器
     * @route /example/index/test_validate
     */
    public function testValidate()
    {
        $validate = new ValidateData();
        $data = [
            "age" => 17,
            "weight" => "50公斤",
            "name" => "ZhangSan",
            "country" => "这里是中国abc",
            "sex" => "未知",
            "mobile" => "11098186452",
        ];

        $rules = [
            "age.required" => "请输入年龄",
            "email.required" => "请输入邮箱",
            "age.gt.18" => "年龄必须大于18",
            "weight.float" => "体重必须为浮点数",
            "name.max.6" => "姓名最大长度为6",
            "country.alphaNum" => "国家必须为数字或者字母",
            "sex.in.男,女" => "性别必须是男或者女",
            "mobile.mobile" => "手机号码不合法",
        ];
        $validate->check($data, $rules);

        var_dump($validate->getErrors());
    }

    /**
     * 测试容器
     * @route /example/index/test_container
     */
    public function testContainer()
    {
        $container = new Container();
        $container->set("app\service\Group", [123]);
        $container->set("app\service\User");
        $container->set("app\service\UserList");
        $group = $container->get("app\service\Group");
        $userList = $container->get("app\service\UserList");
        $group->getA();
        $userList->getUserList();
    }

    /**
     * 测试数据库
     * @route /example/index/test_db
     */
    public function testDb()
    {
        // test update 测试更新
        /*$data = [
            'Sname' => "赵雷1",
        ];
        $dbInstance = Db::getInstance();
        $dbInstance->table('student');
        $dbInstance->where(['Sid' => '01']);
        $result = $dbInstance->update($data);
        var_dump($result);*/

        // test insert 测试插入
        /*$data = [
            'SId' => "14",
            'Sname' => "王八",
            'Sage' => date("Y-m-d"),
            'Ssex' => "男",
        ];
        $dbInstance = Db::getInstance();
        $dbInstance->table('student');
        $result = $dbInstance->insert($data);

        echo $dbInstance->getLastSql();
        var_dump($result);*/

        // test delete 测试删除
        /*$data = [
            'SId' => "14",
        ];
        $dbInstance = Db::getInstance();
        $dbInstance->table('student')->where($data);
        $result = $dbInstance->delete();
        if (!$result) {
            echo $dbInstance->getError();
        }
        echo $dbInstance->getLastSql();*/

        // test select 测试查询
        /*$dbInstance = Db::getInstance();
        $result = $dbInstance->table('student')->where('SId in (01, 02, 13)')->order("SId DESC")->select();
        if (!$result) {
            echo $dbInstance->getError();
        } else {
            foreach ($result as $key => $value) {
                foreach ($value as $v) {
                    echo $v . " ";
                }
                echo "<br/>";
            }
        }*/

        // test query 测试原生执行sql
        /*$dbInstance = Db::getInstance();
//        $sql = "SELECT * FROM student WHERE SId in (01, 02, 13) ORDER BY SId DESC";
        $sql = "INSERT INTO `my_test`.`student`(`SId`, `Sname`, `Sage`, `Ssex`) VALUES ('18', '孙七', '2018-01-01 00:00:00', '女');";
        $result = $dbInstance->query($sql);
        var_dump($result);*/

        // test transaction 测试事务
        /*$dbInstance = Db::getInstance();
        $sql1 = "INSERT INTO `my_test`.`student`(`SId`, `Sname`, `Sage`, `Ssex`) VALUES ('19', '张无忌', '2018-01-01 00:00:00', '男');";
        $sql2 = "INSERT INTO `my_test`.`student`(`SId`, `Sname`, `Sage`, `Ssex`) VALUES ('20', '周芷若', '2018-01-01 00:00:00', '女');";

        $dbInstance->begin();
        try {
            $result1 = $dbInstance->query($sql1);
            trigger_error("触发一条错误");
            $result2 = $dbInstance->query($sql2);
            $dbInstance->commit();
            echo "事务执行成功";
        } catch (Exception $exception) {
            $dbInstance->rollback();
            echo "事务执行失败";
        }*/
    }
}
