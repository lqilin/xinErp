<?php
declare (strict_types=1);

namespace app\subscribe\user;

use app\logic\user\userActionLogLogic;
use app\model\authModel;
use think\facade\Log;

class userSubscribe
{
    /**
     * 事件名称：用户登录
     */
    const  USER_LOGIN = 'UserLogin';

    /**
     * 用户登录
     * @param $event
     */
    public function onUserLogin($event)
    {
        list($loginUser, $token, $loginTerminal) = $event;
        switch ($loginTerminal) {
            //后台管理员登录
            case authModel::AUTH_ADMIN:
                $this->adminUserLogin($loginUser, $token, $loginTerminal);
                break;
            //平台管理员登录
            case authModel::AUTH_SPREAD:
                $this->spreadUserLogin($loginUser, $token, $loginTerminal);
                break;
            //店铺管理员登录
            case authModel::AUTH_SHOP:
                $this->shopUserLogin($loginUser, $token, $loginTerminal);
                break;
            //客户登录
            case authModel::AUTH_CUSTOM:
                $this->customUserLogin($loginUser, $token, $loginTerminal);
                break;
            default:
                Log::notice('Unexpected login information.', [$loginUser, $token, $loginTerminal]);
        }
    }

    /**
     * 后台管理员登录
     * @param array $adminUser 管理员信息
     * @param array $token token信息
     * @param int $loginTerminal 角色权限
     */
    private function adminUserLogin($adminUser, $token, int $loginTerminal)
    {
        $saveData = [
            'object_id' => $adminUser['id'],
            'user_id' => $adminUser['user_id'],
            'last_visit_time' => time(),
            'object' => '后台管理员：' . $adminUser['user']['user_nickname'] . '，在' . date('Y-m-d H:i:s') . '登录后台',
            'app' => request()->url(),
            'action' => request()->action(),
            'controller' => request()->controller(),
            'ip' => request()->ip(),
            'content' => json_encode([$token]),
            'login_terminal' => $loginTerminal,
        ];
        try {
            userActionLogLogic::create($saveData);
        } catch (\Exception $exception) {
            Log::error('UserSubscribe adminUserLogin onInitLogin fail this:' . $exception->getMessage(), [$saveData]);
        }
    }

    /**
     * 平台管理员登录
     * @param array $spreadUser 平台管理员信息
     * @param array $token token信息
     * @param int $loginTerminal 角色权限
     */
    private function spreadUserLogin($spreadUser, $token, int $loginTerminal)
    {
        $saveData = [
            'object_id' => $spreadUser['id'],
            'user_id' => $spreadUser['user_id'],
            'last_visit_time' => time(),
            'object' => '平台管理员：' . $spreadUser['user']['user_nickname'] . '，在' . date('Y-m-d H:i:s') . '登录平台：' . $spreadUser['spread']['spread_name'],
            'app' => request()->url(),
            'action' => request()->action(),
            'controller' => request()->controller(),
            'ip' => request()->ip(),
            'content' => json_encode([$spreadUser, $token]),
            'login_terminal' => $loginTerminal,
        ];
        try {
            userActionLogLogic::create($saveData);
        } catch (\Exception $exception) {
            Log::error('UserSubscribe spreadUserLogin onInitLogin fail this:' . $exception->getMessage(), [$saveData]);
        }
    }

    /**
     * 店铺用户登录
     * @param array $shopUser 店铺管理员信息
     * @param array $token token信息
     * @param int $loginTerminal 角色权限
     */
    private function shopUserLogin($shopUser, $token, int $loginTerminal)
    {
        $saveData = [
            'object_id' => $shopUser['id'],
            'user_id' => $shopUser['user_id'],
            'spread_id' => $shopUser['spread_id'],
            'shop_id' => $shopUser['shop_id'],
            'last_visit_time' => time(),
            'object' => '店铺管理员：' . $shopUser['user']['user_nickname'] . '，在' . date('Y-m-d H:i:s') . '登录店铺：' . $shopUser['shop']['shop_name'] . '(' . $shopUser['spread']['spread_name'] . ')',
            'app' => request()->url(),
            'action' => request()->action(),
            'controller' => request()->controller(),
            'ip' => request()->ip(),
            'content' => json_encode([$token]),
            'login_terminal' => $loginTerminal,
        ];
        try {
            userActionLogLogic::create($saveData);
        } catch (\Exception $exception) {
            Log::error('UserSubscribe shopUserLogin onInitLogin fail this:' . $exception->getMessage(), [$saveData]);
        }
    }

    /**
     * 客户登录
     * @param array $customUser 客户信息
     * @param array $token token信息
     * @param int $loginTerminal 角色权限
     */
    private function customUserLogin($customUser, $token, int $loginTerminal)
    {
        $saveData = [
            'object_id' => $customUser['id'],
            'user_id' => $customUser['user_id'],
            'spread_id' => $customUser['spread_id'],
            'last_visit_time' => time(),
            'object' => '平台客户：' . $customUser['user']['user_nickname'] . '，在' . date('Y-m-d H:i:s') . '登录平台：' . $customUser['spread']['spread_name'],
            'app' => request()->url(),
            'action' => request()->action(),
            'controller' => request()->controller(),
            'ip' => request()->ip(),
            'content' => json_encode([$token]),
            'login_terminal' => $loginTerminal,
        ];
        try {
            userActionLogLogic::create($saveData);
        } catch (\Exception $exception) {
            Log::error('UserSubscribe customUserLogin onInitLogin fail this:' . $exception->getMessage(), [$saveData]);
        }
    }
}
