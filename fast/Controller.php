<?php

namespace fast;


use fast\http\Request;

abstract class Controller
{
    protected object $request;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->request = (new Request())->init();
    }

}