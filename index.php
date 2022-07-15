<?php

define("FAST_PATH", $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "fast");

// 初始化
include_once FAST_PATH . DIRECTORY_SEPARATOR . "App.php";
(new \fast\App())->init();