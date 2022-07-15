<?php
/**
 * Created by: tjx
 * Date: 2021-08-31
 */

namespace app\service;


class UserList
{
    public function __construct(User $user)
    {

    }

    public function getUserList()
    {
        echo "this is the user-list";
    }
}