<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 管理员逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-07 10:48
 * @package app\logic\admin
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\admin;

use app\library\exceptions\ErpAuthException;
use app\library\exceptions\ErpException;
use app\library\interfaces\AuthInterface;
use app\logic\baseLogic;
use app\logic\spread\spreadLogic;
use app\logic\user\userLogic;
use app\logic\user\userTokenLogic;
use app\model\admin\adminUserModel;
use app\library\traits\jwtAuthTrait;
use app\model\authModel;
use app\model\codeModel;
use app\subscribe\user\userSubscribe;
use app\validate\authValidate;
use think\exception\ValidateException;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class adminUserLogic extends baseLogic implements AuthInterface
{
    use SoftDelete;
    use jwtAuthTrait;

    /**
     * @var string 数据表名
     */
    protected $name = 'admin_user';

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
     * @var array 关联表信息
     */
    protected $relevance = [
        'spread',
        'user',
    ];

    /**
     * 初始化
     * spreadLogic constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->autoObject['spread_id'] = $this->getSpreadId();
        $this->setWithModel();
    }

    /**
     * @return BelongsTo 关联平台表
     */
    public function spread(): BelongsTo
    {
        return $this->belongsTo(spreadLogic::class, 'spread_id');
    }

    /**
     * @return BelongsTo 关联用户表
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(userLogic::class, 'user_id');
    }

    /**
     * 用户登录
     * @param array $param
     * @return userTokenLogic|array|bool|mixed|\think\Model|null
     * @throws ErpAuthException
     * @throws ErpException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function login(array $param)
    {
        try {
            validate(authValidate::class)->scene('adminLogin')->check($param);
        } catch (ValidateException $exception) {
            throw new ErpAuthException($exception->getError(), codeModel::AUTH_FAIL);
        }
        if (!list($admin) = $this->loginUser($param['username'], $param['password'])) {
            throw new ErpAuthException(getLastError(), codeModel::AUTH_FAIL);
        }
        return $this->pullToken($admin);
    }

    /**
     * 获取token信息
     * @param adminUserLogic $admin
     * @return userTokenLogic|array|\think\Model|null
     * @throws ErpAuthException
     * @throws ErpException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function pullToken(adminUserLogic $admin)
    {
        $oldToken = userTokenLogic::getInstance()
            ->where(
                [
                    'role_id' => $admin['id'],
                    'device_type' => request()->device_type,
                    'login_terminal' => authModel::AUTH_ADMIN,
                ]
            )->find();
        if (!empty($oldToken)) {
            if ($oldToken['expire_time'] <= time()) {
                $token = userTokenLogic::getInstance()->updateToken($oldToken, $admin, authModel::AUTH_ADMIN, request()->device_type);
            } else {
                $token = $oldToken;
            }
        } else {
            $token = userTokenLogic::getInstance()->createToken($admin, authModel::AUTH_ADMIN, request()->device_type);
        }
        //用户登录事件
        event(userSubscribe::USER_LOGIN, [$admin, $token, authModel::AUTH_ADMIN]);
        $this->tokenData = $token;
        return $token;
    }

    /**
     * 用户登录权限验证
     * @param array          $list
     * @param userTokenLogic $tokenData
     * @return array
     * @throws ErpAuthException
     */
    public function authPass(array $list, userTokenLogic $tokenData)
    {
        [$adminUser, $type, $deviceType] = $list;
        if (empty($adminUser)) {
            throw new ErpAuthException('管理员信息不存在或已被删除', codeModel::NO_AUTH);
        }
        if ($adminUser['state'] != adminUserModel::STATE_OK) {
            throw new ErpAuthException('管理员信息已被禁用', codeModel::AUTH_FAIL);
        }
        if (empty($adminUser['user'])) {
            throw new ErpAuthException('用户信息不存在或已被禁用', codeModel::NO_AUTH);
        }
        $tokenData->type = $type;
        $adminUser->device_type = $deviceType;
        return compact('adminUser', 'tokenData');
    }

    /**
     * 注册
     * @param array $param
     * @return mixed|void
     * @throws ErpException
     */
    public function register(array $param)
    {
        throw new ErpException('后台未开放注册', codeModel::ERROR);
    }

    /**
     * 退出登录
     * @param int $id
     * @return bool|mixed
     * @throws ErpAuthException
     */
    public function loginOut()
    {
        if (!$this->tokenData->delete()) throw new ErpAuthException('退出登录失败', codeModel::AUTH_FAIL);
        return true;
    }

    /**
     * 注销账号
     * @return bool|mixed
     * @throws ErpAuthException
     * @throws ErpException
     */
    public function cancellation()
    {
        if ($this->fromId == 1) throw new ErpAuthException('系统管理员账户不可注销', codeModel::ERROR);
        if (!$this->authInfo->delete()) throw new ErpAuthException('注销失败，请稍后再试', codeModel::ERROR);
        if ($this->loginOut()) {
            return true;
        }
        throw new ErpAuthException('注销失败，请稍后再试', codeModel::ERROR);
    }

    /**
     * @param int   $userId
     * @param array $hidden
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTokenAdminUser(int $userId, $hidden = [])
    {
        $info = $this
            ->with(['user'])
            ->where('user_id', $userId)
            ->hidden($hidden)
            ->find();
        return $info;
    }

    /**
     * 管理员登录信息
     * @param string $userName
     * @param string $password
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginUser(string $userName, string $password)
    {
        $user = userLogic::getInstance()->loginUser($userName);
        if (empty($user)) {
            recordError('用户信息不存在或已被删除');
            return false;
        }
        $admin = $this->where(['user_id' => $user['id']])->find();
        if (empty($admin)) {
            recordError('管理员信息不存在或已被删除');
            return false;
        }
        if ($admin['state'] != adminUserModel::STATE_OK) {
            recordError('管理员信息未开启或已被禁用');
            return false;
        }
        if (!shellPassword($password, $admin['password'])) {
            recordError('密码输入有误，请重新输入');
            return false;
        }

        $admin['user'] = $user;
        return [$admin];
    }

    /**
     * 注销后台管理员信息
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id)
    {
        $admin = $this->find($id);
        if (empty($admin)) {
            recordError('管理员信息不存在或已被删除');
            return false;
        }
        if ($admin['id'] == 1) {
            recordError('后台总管理员信息不能删除');
            return false;
        }
        if (!$admin->delete()) {
            recordError('管理员信息删除失败，请稍候再试');
            return false;
        }
        return true;
    }
}