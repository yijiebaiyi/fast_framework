<?php

namespace fast\http;


class Request
{
    /**
     * $_SERVER
     * @var array
     */
    protected array $server;

    /**
     * $_GET
     * @var array
     */
    protected array $get;

    /**
     * $_POST
     * @var array
     */
    protected array $post;

    /**
     * $_REQUEST
     * @var array
     */
    protected array $request;

    /**
     * file_get_content("php://input")
     * @var string
     */
    protected string $input;

    /**
     * $_COOKIE
     * @var array
     */
    protected array $cookie;

    /**
     * $_FILES
     * @var array
     */
    protected array $file;

    protected bool $isInit = false;

    public function init(): self
    {
        if (false === $this->isInit) {
            $this->server = $_SERVER;
            $this->get = $_GET;
            $this->post = $_POST;
            $this->input = file_get_contents('php://input');
            $this->request = $_REQUEST;
            $this->cookie = $_COOKIE;
            $this->file = $_FILES ?? [];

            $this->isInit = true;
        }

        return $this;
    }

    /**
     * GET
     * @param string $name
     * @param null $default
     * @return array|mixed|null
     */
    public function get($name = "", $default = null)
    {
        if (empty($name)) {
            return $this->get;
        }
        return $this->get[$name] ?? $default;
    }

    /**
     * POST
     * @param $name
     * @param null $default
     * @return array|mixed|null
     */
    public function post($name, $default = null)
    {
        if (empty($name)) {
            return $this->post;
        }
        return $this->post[$name] ?? $default;
    }

    /**
     * REQUEST
     * @param $name
     * @param null $default
     * @return array|mixed|null
     */
    public function request($name, $default = null)
    {
        if (empty($name)) {
            return $this->post;
        }
        return $this->request[$name] ?? $default;
    }
}