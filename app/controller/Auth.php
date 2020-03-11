<?php
declare (strict_types = 1);

namespace app\controller;

use app\library\exceptions\ErpAuthException;
use app\library\exceptions\ErpException;
use app\library\repositories\authRepository;
use app\middleware\AllowOriginMiddleware;
use app\Request;
use think\annotation\Route;
use think\annotation\route\Middleware;

/**
 * @package app\controller
 * project xinErp
 */
class Auth
{
    /**
     * 登录
     * @param Request $request = [
     *      'username' => '用户名/邮箱/手机号码',
     *      'password' => '123456',     //登录密码
     *      'spread_token' => '7Q9r0XaM', //平台token（用于平台管理员、平台店铺管理员、平台用户登录）
     *      'shop_token' => '64aA2p1V', //店铺token（用于平台店铺管理员登录）
     *      'captcha' => '123456',      //（邮箱）短信验证码（用于发送短信验证码的时候使用）
     * ]
     * @param authRepository $authRepository
     * @inheritDoc https://documenter.getpostman.com/view/6090259/SWECWaGL
     * @Route("login")
     * @Middleware({AllowOriginMiddleware::class})
     */
    public function login(Request $request, authRepository $authRepository)
    {
        try {
            $token = $authRepository->login($request->post());
        }catch (ErpAuthException $exception) {
            apiReturn($exception->getCode(), $exception->getMessage());
        }catch (ErpException $exception) {
            apiReturn($exception->getCode(), $exception->getMessage());
        }
        apiSuccess('登录成功', $token->visible(['token', 'expire_time'])->toArray());
    }
}
