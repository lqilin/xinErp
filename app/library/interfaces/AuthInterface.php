<?php
namespace app\library\interfaces;

interface AuthInterface
{
    /**
     * 登录
     * @param array $param
     * @return mixed
     */
    public function login(array $param);

    /**
     * 注册
     * @param array $param
     * @return mixed
     */
    public function register(array $param);

    /**
     * 退出登录
     * @return mixed
     */
    public function loginOut();

    /**
     * 注销账户
     * @return mixed
     */
    public function cancellation();
}