<?php
declare (strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\authModel;
use app\model\codeModel;
use app\Request;

class Base extends BaseController
{
    /**
     * @var int
     */
    protected $auth = 0;

    protected function initialize()
    {
        $this->auth = authModel::AUTH_CUSTOM + authModel::AUTH_SHOP + authModel::AUTH_SPREAD + authModel::AUTH_ADMIN;
    }

    /**
     * 检测权限
     * @param Request $request
     * @param int $auth
     * @return bool
     */
    public function checkAuthority(int $auth)
    {
        if (empty($auth)) {
            $auth = $this->auth;
        }
        if (isset($this->request->loginTerminal)) {
            if ($this->request->loginTerminal & $auth) {
                return true;
            }
        }
        apiReturn(codeModel::NO_AUTH, '没有对应权限，请稍后再试');
    }
}
 