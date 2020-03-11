<?php

namespace app\library\traits;

use app\library\exceptions\ErpAuthException;
use app\logic\shop\shopLogic;
use app\logic\spread\spreadLogic;
use app\logic\user\userLogic;
use app\logic\user\userTokenLogic;
use app\model\codeModel;
use app\model\shop\shopModel;
use app\model\spread\spreadModel;
use think\Model;

trait erpAuthTrait
{
    /**
     * 自动写入的字段信息
     *
     * @var array
     */
    protected $autoObject = [];

    /**
     * 是否刷新最新的数据
     *
     * @var bool
     */
    private $renovate = false;

    /**
     * 用户信息
     *
     * @var userLogic null
     */
    protected $userInfo = null;

    /**
     * 平台信息
     *
     * @var spreadLogic null
     */
    protected $spreadInfo = null;

    /**
     * 店铺信息
     *
     * @var shopLogic null
     */
    protected $shopInfo = null;

    /**
     * @var Model null 登录角色信息
     */
    protected $authInfo = null;

    /**
     * @var userTokenLogic token信息
     */
    protected $tokenData = null;

    /**
     * @var int 平台id
     */
    protected $spreadId = 0;

    /**
     * @var int 店铺id
     */
    protected $shopId = 0;

    /**
     * @var int 用户id
     */
    protected $userId = 0;

    /**
     * @var int 来源id
     */
    protected $fromId = 0;

    /**
     * @var int 登录角色
     */
    protected $loginTerminal = 0;

    /**
     * @var bool 是否登录
     */
    protected $isLogin = false;

    /**
     * 设置是否登录
     *
     * @return void
     */
    public function setIsLogin(): void
    {
        $this->isLogin = !is_null($this->authInfo);
    }

    /**
     * @return bool
     */
    public function getIsLogin(): bool
    {
        return $this->isLogin;
    }

    /**
     * 设置用户信息
     *
     * @param userLogic $userInfo
     *
     * @return void
     */
    public function setUserInfo(userLogic $userInfo): void
    {
        $this->userInfo = $userInfo;
    }

    /**
     * 获取用户信息
     *
     * @return userLogic
     */
    public function getUserInfo(): userLogic
    {
        return $this->userInfo;
    }

    /**
     * 设置平台信息
     *
     * @param spreadLogic $spreadInfo
     *
     * @return void
     */
    public function setSpreadInfo(spreadLogic $spreadInfo): void
    {
        $this->spreadInfo = $spreadInfo;
    }

    /**
     * 获取平台信息
     *
     * @return spreadLogic
     */
    public function getSpreadInfo(): spreadLogic
    {
        return $this->spreadInfo;
    }

    /**
     * 设置店铺信息
     *
     * @param shopLogic $shopInfo
     *
     * @return void
     */
    public function setShopInfo(shopLogic $shopInfo): void
    {
        $this->shopInfo = $shopInfo;
    }

    /**
     * 获取店铺信息
     *
     * @return shopLogic
     */
    public function getShopInfo(): shopLogic
    {
        return $this->shopInfo;
    }

    /**
     * 设置授权用户信息
     *
     * @param $authInfo
     *
     * @return void
     */
    public function setAuthInfo($authInfo): void
    {
        $this->authInfo = $authInfo;
    }

    /**
     * 获取授权用户信息
     *
     * @return Model
     */
    public function getAuthInfo(): Model
    {
        return $this->authInfo;
    }

    /**
     * 设置用户登录token
     * @param userTokenLogic $tokenData
     * @return void
     */
    public function setTokenData(userTokenLogic $tokenData): void
    {
        $this->tokenData = $tokenData;
    }

    /**
     * 获取用户登录token
     * @return userTokenLogic
     */
    public function getTokenData()
    {
        return $this->tokenData;
    }

    /**
     * 设置用户id
     *
     * @param int $userId
     *
     * @return void
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * 设置用户ID
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * 设置平台id
     *
     * @param int $spreadId
     *
     * @return void
     */
    public function setSpreadId(int $spreadId): void
    {
        $this->spreadId = $spreadId;
    }

    /**
     * 获取平台ID
     *
     * @return int
     */
    public function getSpreadId(): int
    {
        return $this->spreadId;
    }

    /**
     * 设置店铺ID
     *
     * @param int $shopId
     *
     * @return void
     */
    public function setShopId(int $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * 获取店铺ID
     *
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    /**
     * 设置当前登录的ID
     *
     * @param int $fromId
     *
     * @return void
     */
    public function setFromId(int $fromId): void
    {
        $this->fromId = $fromId;
    }

    /**
     * 获取当前登录的ID
     *
     * @return int
     */
    public function getFromId(): int
    {
        return $this->fromId;
    }

    /**
     * 设置登录类型：权限：1客户 2店铺 4平台 8管理员
     *
     * @param int $loginTerminal
     *
     * @return void
     */
    public function setLoginTerminal(int $loginTerminal): void
    {
        $this->loginTerminal = $loginTerminal;
    }

    /**
     * 获取登录类型：权限：1客户 2店铺 4平台 8管理员
     *
     * @return int
     */
    public function getLoginTerminal(): int
    {
        return $this->loginTerminal;
    }

    /**
     * 验证当前平台状态
     *
     * @return bool
     * @throws ErpAuthException
     */
    public function checkSpreadState()
    {
        if (empty($this->spreadInfo)) {
            throw new ErpAuthException('平台信息不存在或已被删除', codeModel::NO_AUTH);
        }

        if ($this->spreadInfo['state'] != spreadModel::STATE_OK) {
            throw new ErpAuthException('平台已被禁用或下架', codeModel::AUTH_FAIL);
        }
        return true;
    }

    /**
     * 店铺状态
     *
     * @return bool
     * @throws ErpAuthException
     */
    public function checkShopState()
    {
        if (empty($this->shopInfo)) {
            throw new ErpAuthException('店铺信息不存在或已被删除', codeModel::NO_AUTH);
        }

        if ($this->shopInfo['state'] != shopModel::STATE_OK) {
            throw new ErpAuthException('店铺已被禁用或下架', codeModel::AUTH_FAIL);
        }
        return true;
    }
}