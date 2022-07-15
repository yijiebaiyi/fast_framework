<?php
declare (strict_types = 1);

namespace fast;


use fast\http\Request;

class Shutdown
{
    public function construct()
    {

    }

    public function init()
    {
        register_shutdown_function(array(__CLASS__, "handler"));
    }

    public static function handler()
    {
        //
    }
}