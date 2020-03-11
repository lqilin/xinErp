<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 店铺用户逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-07 10:47
 * @package app\logic\shop
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\shop;

use app\library\exceptions\ErpAuthException;
use app\library\exceptions\ErpException;
use app\library\interfaces\AuthInterface;
use app\logic\baseLogic;
use app\logic\spread\spreadLogic;
use app\logic\user\userLogic;
use app\logic\user\userTokenLogic;
use app\model\authModel;
use app\model\codeModel;
use app\model\custom\customModel;
use app\model\shop\shopModel;
use app\model\shop\shopUserModel;
use app\model\spread\spreadModel;
use app\library\traits\jwtAuthTrait;
use app\subscribe\user\userSubscribe;
use app\validate\authValidate;
use think\exception\ValidateException;
use think\model\concern\SoftDelete;

class shopUserLogic extends baseLogic implements AuthInterface
{
    use SoftDelete;
    use jwtAuthTrait;

    /**
     * @var string 数据表名
     */
    protected $name = 'shop_user';

    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var bool 是否开启字段时间戳字段
     */
    protected $autoWriteTimestamp = true;

    /**
     * @var string 默认软删除字段
     */
    protected $deleteTime = 'delete_time';

    /**
     * @var int 默认软删除时间
     */
    protected $defaultSoftDelete = 0;

    /**
     * 初始化
     * spreadLogic constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->autoObject['spread_id'] = $this->getSpreadId();
    }

    /**
     * 登录
     * @param array $param
     * @return userTokenLogic|array|mixed|\think\Model|null
     * @throws ErpAuthException
     * @throws ErpException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login(array $param)
    {
        try {
            validate(authValidate::class)->scene('shopLogin')->check($param);
        } catch (ValidateException $exception) {
            throw new ErpAuthException($exception->getError(), codeModel::AUTH_FAIL);
        }
        if (!list($shopUser) = $this->loginUser(decodeStr($param['spread_token']), decodeStr($param['shop_token']), $param['username'], $param['password'])) {
            apiReturn(codeModel::ERROR, getLastError());
        }
        return $this->pullToken($shopUser);
    }

    public function register(array $param)
    {
        // TODO: Implement register() method.
    }

    /**
     * 退出登录
     * @return bool|mixed
     * @throws ErpAuthException
     */
    public function loginOut()
    {
        if (!$this->tokenData->delete()) throw new ErpAuthException('退出登录失败', codeModel::AUTH_FAIL);
        return true;
    }

    /**
     * 注销账户
     * @return bool|mixed
     * @throws ErpAuthException
     * @throws ErpException
     */
    public function cancellation()
    {
        if (!$this->authInfo->delete()) throw new ErpException('注销失败，请稍后再试', codeModel::AUTH_FAIL);
        if ($this->loginOut()) {
            return true;
        }
        throw new ErpException('注销失败，请稍后再试', codeModel::AUTH_FAIL);
    }

    /**
     * @param $list
     * @param userTokenLogic $tokenData
     * @return array|bool
     * @throws ErpAuthException
     */
    public function authPass($list, userTokenLogic $tokenData)
    {
        [$shopUser, $type, $deviceType] = $list;
        if (empty($shopUser)) {
            recordError('商家信息不存在或已被删除');
            return false;
        }
        if ($shopUser['state'] != customModel::STATE_OK) {
            recordError('商家信息已被平台禁用，请联系平台管理员');
            return false;
        }
        if (empty($shopUser['user'])) {
            recordError('用户信息不存在或已被禁用');
            return false;
        }
        if (empty($shopUser['shop'])) {
            recordError('店铺信息不存在或已被删除');
            return false;
        }
        if ($shopUser['shop']['state'] != shopModel::STATE_OK) {
            recordError('店铺信息已被平台禁用，请联系平台管理员');
            return false;
        }
        $this->setSpreadInfo($shopUser['spread']);
        $this->setShopInfo($shopUser['shop']);
        if (!$this->checkSpreadState()) {
            return false;
        }
        if (!$this->checkShopState()) {
            return false;
        }
        if ($shopUser['id'] != $tokenData['role_id']) {
            recordError('登录状态有误');
            return false;
        }
        $tokenData->type = $type;
        $shopUser->device_type = $deviceType;
        return compact('shopUser', 'tokenData');
    }

    /**
     * 获取token信息
     * @param shopUserLogic $shopUser
     * @return userTokenLogic|array|\think\Model|null
     * @throws ErpAuthException
     * @throws \app\library\exceptions\ErpException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function pullToken(shopUserLogic $shopUser)
    {
        $oldToken = userTokenLogic::getInstance()
            ->where(
                [
                    'role_id' => $shopUser['id'],
                    'device_type' => request()->device_type,
                    'login_terminal' => authModel::AUTH_SHOP
                ]
            )->find();
        if (!empty($oldToken)) {
            if ($oldToken['expire_time'] <= time()) {
                $token = userTokenLogic::getInstance()->updateToken($oldToken, $shopUser, authModel::AUTH_SHOP, request()->device_type);
            } else {
                $token = $oldToken;
            }
        } else {
            $token = userTokenLogic::getInstance()->createToken($shopUser, authModel::AUTH_SHOP, request()->device_type);
        }
        //用户登录事件
        event(userSubscribe::USER_LOGIN, [$shopUser, $token, authModel::AUTH_SHOP]);
        $this->tokenData = $token;
        return $token;
    }

    /**
     * 店铺用户信息登录
     * @param int $spreadId
     * @param int $shopId
     * @param string $userName
     * @param string $password
     * @return array|\think\Model|null
     * @throws ErpAuthException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginUser(int $spreadId, int $shopId, string $userName, string $password)
    {
        $user = userLogic::getInstance()->loginUser($userName);
        if (empty($user)) {
            throw new ErpAuthException('平台用户信息不存在', codeModel::NO_AUTH);
        }
        $spread = spreadLogic::getInstance()->find($spreadId);
        if (empty($spread)) {
            throw new ErpAuthException('平台信息不存在或已被删除', codeModel::NO_AUTH);
        }
        if ($spread['state'] != spreadModel::STATE_OK) {
            throw new ErpAuthException('平台未启用或已被禁用', codeModel::AUTH_FAIL);
        }
        $shop = shopLogic::getInstance()->where(['spread_id' => $spreadId, 'id' => $shopId])->find();
        if (empty($shop)) {
            throw new ErpAuthException('店铺信息不存在或已被删除', codeModel::NO_AUTH);
        }
        if ($shop['state'] != shopModel::STATE_OK) {
            throw new ErpAuthException('店铺未开放或已被禁用', codeModel::AUTH_FAIL);
        }
        $shopUser = $this->where(['spread_id' => $spreadId, 'shop_id' => $shopId, 'user_id' => $user['id']])->find();
        if (!empty($shopUser)) {
            throw new ErpAuthException('店铺用户信息不存在或已被删除', codeModel::NO_AUTH);
        }
        if ($shopUser['state'] != shopUserModel::STATE_OK) {
            throw new ErpAuthException('店铺用户信息已被禁用或未授权', codeModel::AUTH_FAIL);
        }
        if (!shellPassword($password, $shopUser['password'])) {
            throw new ErpAuthException('密码错误,请重新输入', codeModel::AUTH_FAIL);
        }

        $shopUser['user'] = $user;
        $shopUser['spread'] = $spread;
        $shopUser['shop'] = $shop;

        return $shopUser;
    }

    /**
     * 删除店铺管理员信息
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id)
    {
        $shopUser = $this->find($id);
        if (empty($shopUser)) {
            recordError('店铺管理员信息不存在或已被删除');
            return false;
        }
        if ($shopUser['is_master'] == shopUserModel::IS_MASTER) {
            recordError('该管理员为店铺所属人');
            return false;
        }
        if (!$shopUser->delete()) {
            recordError('店铺管理员信息注销失败，请稍候再试');
            return false;
        }
        return true;
    }
}