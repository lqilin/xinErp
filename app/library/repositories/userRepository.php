<?php

namespace app\library\repositories;

use app\library\exceptions\ErpAuthException;
use app\library\traits\instanceTrait;
use app\model\codeModel;
use app\logic\user\userTokenLogic;
use think\db\exception\ModelNotFoundException;
use think\db\exception\DataNotFoundException;

/**
 * Class UserRepository
 * @package crmeb\repositories
 */
class userRepository
{
    use instanceTrait;

    protected $auth;

    protected $logic;

    /**
     * userRepository constructor.
     * @param $loginTerminal
     * @throws ErpAuthException
     */
    public function __construct()
    {
        $this->auth = config('erp.auth_logic');
    }

    /**
     * 授权用户登录
     * @param userRepository $user
     * @param $token
     * @return array
     * @throws DataNotFoundException
     * @throws ErpAuthException
     * @throws ModelNotFoundException
     * @throws \think\db\exception\DbException
     */
    public function parseToken($token): array
    {
        if (!$token || !$tokenData = userTokenLogic::getInstance()->where('token', $token)->find()) {
            throw new ErpAuthException('请登录', codeModel::AUTH_FAIL);
        }
        if (!isset($this->auth[$tokenData['login_terminal']])) {
            throw new ErpAuthException('身份验证错误', codeModel::NO_AUTH);
        }
        $this->logic = new $this->auth[$tokenData['login_terminal']];
        try {
            $list = $this->logic->parseToken($token);
        }catch (\Throwable $exception) {
            throw new ErpAuthException('登录已过期,请重新登录', codeModel::AUTH_FAIL);
        }
        return $this->logic->authPass($list, $tokenData);
    }
}