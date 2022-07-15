<?php
/**
 * Created by: tjx
 * Date: 2021-07-19
 */

namespace app\index\controller;

use fast\Controller;
use fast\util\Upload;

class IndexController extends Controller
{
    public function index()
    {
        echo "this is index/index/index";
    }

    public function test1()
    {
        echo "this is index/index/index1";
    }

    public function upload()
    {
        $upload = new Upload();
        $upload->uploadOne("", "");
    }
}