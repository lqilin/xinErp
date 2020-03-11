<?php
/**
 * 用户登录授权中间件
 */
namespace app\middleware;

use app\library\exceptions\ErpAuthException;
use app\library\exceptions\ErpException;
use app\library\traits\erpAuthTrait;
use app\model\authModel;
use app\Request;
use app\library\interfaces\MiddlewareInterface;
use app\library\repositories\userRepository;

class AuthTokenMiddleware implements MiddlewareInterface
{
    use erpAuthTrait;

    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function handle(Request $request, \Closure $next)
    {
        $token = trim(ltrim($request->header('Authori-zation'), 'Bearer'));
        if (!$token) {
            $token = trim(ltrim($request->header('Authorization'), 'Bearer'));//正式版，删除此行，某些服务器无法获取到token调整为 Authori-zation
        }
        try {
            $authInfo = userRepository::getInstance()->parseToken($token);
            $this->setAuthData($authInfo, $request);
        }catch (ErpAuthException $exception) {
            apiReturn($exception->getCode(), $exception->getMessage());
        }catch (ErpException $exception) {
            apiReturn($exception->getCode(), $exception->getMessage());
        }
        return $next($request);
    }

    /**
     * @param array $authInfo
     * @param Request $request
     */
    private function setAuthData(array $authInfo, Request $request)
    {
        $this->setLoginTerminal($authInfo['tokenData']['login_terminal']);
        $this->setTokenData($authInfo['tokenData']);
        switch ($authInfo['tokenData']['login_terminal']) {
            case authModel::AUTH_CUSTOM:
                $this->setCustomData($authInfo, $request);
                break;
            case authModel::AUTH_SHOP:
                $this->setShopData($authInfo, $request);
                break;
            case authModel::AUTH_SPREAD:
                $this->setSpreadData($authInfo, $request);
                break;
            case authModel::AUTH_ADMIN:
                $this->setAdminData($authInfo, $request);
                break;
        }
        $request->fromId = $this->getFromId();
        $request->loginTerminal = $this->getLoginTerminal();
        $request->tokenData = $this->getTokenData();
        $request->isLogin = $this->getIsLogin();
        $request->userInfo = $this->getUserInfo();
        $request->authInfo = $this->getAuthInfo();
    }

    /**
     * 设置客户登录请求信息
     *
     * @param array   $authInfo
     * @param Request $request
     */
    public function setCustomData(array $authInfo, Request $request)
    {
        $this->setFromId($authInfo['custom']['id']);
        $this->setUserId($authInfo['custom']['user_id']);
        $this->setSpreadId($authInfo['custom']['spread_id']);
        $this->setAuthInfo($authInfo['custom']);
        $this->setUserInfo($authInfo['custom']['user']);
        $this->setSpreadInfo($authInfo['custom']['spread']);
        $this->setIsLogin();
        $request->spreadId = $this->getSpreadId();
        $request->spreadInfo = $this->getSpreadInfo();
    }

    /**
     * 设置店铺管理员登录请求信息
     *
     * @param array   $authInfo
     * @param Request $request
     */
    public function setShopData(array $authInfo, Request $request)
    {
        $this->setFromId($authInfo['shopUser']['id']);
        $this->setUserId($authInfo['shopUser']['user_id']);
        $this->setSpreadId($authInfo['shopUser']['spread_id']);
        $this->setShopId($authInfo['shopUser']['shop_id']);
        $this->setAuthInfo($authInfo['shopUser']);
        $this->setUserInfo($authInfo['shopUser']['user']);
        $this->setSpreadInfo($authInfo['shopUser']['spread']);
        $this->setShopInfo($authInfo['shopUser']['spread']);
        $this->setIsLogin();
        $request->spreadId = $this->getSpreadId();
        $request->spreadInfo = $this->getSpreadInfo();
        $request->shopId = $this->getShopId();
        $request->shopInfo = $this->getShopInfo();
    }

    /**
     * 设置平台管理员登录请求信息
     *
     * @param array   $authInfo
     * @param Request $request
     */
    public function setSpreadData(array $authInfo, Request $request)
    {
        $this->setFromId($authInfo['spreadUser']['id']);
        $this->setUserId($authInfo['spreadUser']['user_id']);
        $this->setSpreadId($authInfo['spreadUser']['spread_id']);
        $this->setAuthInfo($authInfo['spreadUser']);
        $this->setUserInfo($authInfo['spreadUser']['user']);
        $this->setSpreadInfo($authInfo['spreadUser']['spread']);
        $this->setIsLogin();
        $request->spreadId = $this->getSpreadId();
        $request->spreadInfo = $this->getSpreadInfo();
    }

    /**
     * 设置后台管理员登录请求信息
     *
     * @param array   $authInfo
     * @param Request $request
     */
    public function setAdminData(array $authInfo)
    {
        $this->setFromId($authInfo['adminUser']['id']);
        $this->setUserId($authInfo['adminUser']['user_id']);
        $this->setAuthInfo($authInfo['adminUser']);
        $this->setUserInfo($authInfo['adminUser']['user']);
        $this->setIsLogin();
    }
}