<?php
namespace app\library\repositories;

use app\library\exceptions\ErpAuthException;
use app\model\codeModel;

class authRepository
{
    /**
     * @var object 权限对象
     */
    protected $auth;

    /**
     * @var mixed 涉及到的逻辑类
     */
    protected $config;

    /**
     * authRepository constructor.
     * @param int $loginTerminal
     * @throws ErpAuthException
     */
    public function __construct()
    {
        $this->config = config('erp.auth_logic');
        if (!isset($this->config[loginTerminal()])) {
            throw new ErpAuthException('用户身份验证错误', codeModel::NO_AUTH);
        }
        $this->auth = $this->config[loginTerminal()]::getInstance();
    }

    /**
     * 登录
     * @param array $param
     * @return mixed
     */
    public function login(array $param)
    {
        return $this->auth->login($param);
    }

    /**
     * 注册
     * @param array $param
     * @return mixed
     */
    public function register(array $param)
    {
        return $this->auth->register($param);
    }
}
