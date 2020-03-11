<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 平台用户信息逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-06 16:37
 * @package app\logic\user
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\spread;

use app\library\exceptions\ErpAuthException;
use app\library\exceptions\ErpException;
use app\library\interfaces\AuthInterface;
use app\logic\user\userTokenLogic;
use app\model\authModel;
use app\model\codeModel;
use app\model\commonModel;
use app\model\spread\spreadModel;
use app\model\spread\spreadUserModel;
use app\logic\user\userLogic;
use app\logic\baseLogic;
use app\library\traits\jwtAuthTrait;
use app\subscribe\user\userSubscribe;
use app\validate\authValidate;
use think\db\Query;
use think\exception\ValidateException;
use think\model\concern\SoftDelete;

class spreadUserLogic extends baseLogic implements AuthInterface
{
    use SoftDelete;             //软删除
    use jwtAuthTrait;           //jwt

    /**
     * @var string 数据表名
     */
    protected $name = 'spread_user';

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
     * 关联平台信息
     * @return \think\model\relation\HasOne
     */
    public function spread()
    {
        return $this->hasOne(spreadLogic::class, 'id', 'spread_id');
    }

    /**
     * 关联用户信息
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(userLogic::class, 'id', 'user_id');
    }

    /**
     * 状态搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchStateAttr(Query $query, $value, $data)
    {
        if (in_array($value, [spreadUserModel::STATE_OK, spreadUserModel::STATE_NO_AUTH, spreadUserModel::STATE_FORBIDDEN])) {
            $query->where('state', $value);
        }
    }

    /**
     * 关键词搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchKeywordAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            if (isMobile($value)) {
                $query->where('user.mobile', $value);
            } elseif (isEmail($value)) {
                $query->where('user.user_email', $value);
            } else {
                $query->whereLike('user.user_nickname', '%' . $value . '%');
            }
        }
    }

    /**
     * 创建时间检索
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr(Query $query, $value, $data)
    {
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            $query->whereBetweenTime('spread_user_logic.create_time', $data['start_time'], $data['end_time']);
        } else {
            if (!empty($data['start_time'])) {
                $query->whereTime('spread_user_logic.create_time', '>=', $data['start_time']);
            } elseif (!empty($data['end_time'])) {
                $query->whereTime('spread_user_logic.create_time', '<=', $data['end_time']);
            }
        }
    }

    /**
     * 获取平台用户列表
     * @param array $param
     * @param int $page
     * @param int $size
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getUserList(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'count' => commonModel::DEFAULT_COUNT,
            'data' => [],
        ];

        $data = $this
            ->withJoin(['user'])
            ->withSearch(['id', 'state', 'create_time', 'keyword'], $param)
            ->visible(['user' => ['user_nickname', 'user_email', 'mobile']])
            ->order('create_time DESC')
            ->paginate($size);

        $data->each(function ($item) {
            unset($item['password']);
            return $item;
        });

        $result['count'] = $data->count();
        $result['last_page'] = $data->lastPage();
        $result['data'] = $data->items();
        return $result;
    }

    /**
     * 平台管理员登录
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
            validate(authValidate::class)->scene('spreadLogin')->check($param);
        } catch (ValidateException $exception) {
            throw new ErpException($exception->getError(), codeModel::ERROR);
        }
        if (!list($spreadUser) = spreadUserLogic::getInstance()->loginUser($param['spread_token'], $param['username'], $param['password'])) {
            apiReturn(codeModel::ERROR, getLastError());
        }
        return $this->pullToken($spreadUser);
    }

    /**
     * 获取token信息
     * @param spreadUserLogic $spreadUser
     * @return userTokenLogic|array|\think\Model|null
     * @throws ErpAuthException
     * @throws ErpException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function pullToken(spreadUserLogic $spreadUser)
    {
        $oldToken = userTokenLogic::getInstance()
            ->where(
                [
                    'role_id' => $spreadUser['id'],
                    'device_type' => request()->device_type,
                    'login_terminal' => authModel::AUTH_SPREAD
                ]
            )->find();
        if (!empty($oldToken)) {
            if ($oldToken['expire_time'] <= time()) {
                $token = userTokenLogic::getInstance()->updateToken($oldToken, $spreadUser, authModel::AUTH_SPREAD, request()->device_type);
            } else {
                $token = $oldToken;
            }
        } else {
            $token = userTokenLogic::getInstance()->createToken($spreadUser, authModel::AUTH_SPREAD, request()->device_type);
        }
        //用户登录事件
        event(userSubscribe::USER_LOGIN, [$spreadUser, $token, authModel::AUTH_SPREAD]);
        $this->tokenData = $token;
        return $token;
    }

    /**
     * @param array $param
     * @return mixed|void
     * @throws ErpException
     * @todo 后台管理员注册
     */
    public function register(array $param)
    {
        try {
            validate(authValidate::class)->scene('spreadRegister')->check($param);
        } catch (ValidateException $exception) {
            throw new ErpException($exception->getError(), codeModel::ERROR);
        }
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
        if (!$this->authInfo->delete()) throw new ErpAuthException('注销失败，请稍后再试', codeModel::AUTH_FAIL);
        if ($this->loginOut()) {
            return true;
        }
        throw new ErpAuthException('注销失败，请稍后再试', codeModel::AUTH_FAIL);
    }

    /**
     * token登录信息
     * @param $list
     * @param $tokenData
     * @return array
     * @throws ErpAuthException
     */
    public function authPass($list, $tokenData)
    {
        [$spreadUser, $type, $deviceType] = $list;
        if (empty($spreadUser)) {
            $tokenData->delete();
            throw new ErpAuthException('平台用户信息不存在或已被删除', codeModel::NO_AUTH);
        }
        if ($spreadUser['state'] != spreadModel::STATE_OK) {
            $tokenData->delete();
            throw new ErpAuthException('平台信息已被平台禁用，请联系系统管理员', codeModel::AUTH_FAIL);
        }
        if (empty($spreadUser['user'])) {
            $tokenData->delete();
            throw new ErpAuthException('用户信息不存在或已被禁用', codeModel::AUTH_FAIL);
        }
        $this->setSpreadInfo($spreadUser['spread']);
        try {
            $this->checkSpreadState();
        } catch (ErpAuthException $exception) {
            $tokenData->delete();
            throw new ErpAuthException($exception->getMessage(), $exception->getCode());
        }
        if ($spreadUser['id'] != $tokenData['role_id']) {
            throw new ErpAuthException('登录状态有误', codeModel::AUTH_FAIL);
        }
        $tokenData->type = $type;
        $spreadUser->device_type = $deviceType;
        return compact('spreadUser', 'tokenData');
    }

    /**
     * 获取平台用户信息
     * @param int $spreadId 平台id
     * @param int $userId 用户id
     * @param array $hidden
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTokenUserInfo(int $spreadId, int $userId, array $hidden = [])
    {
        $data = $this->with(['spread', 'user'])
            ->where('spread_id', $spreadId)
            ->where('user_id', $userId)
            ->hidden($hidden)
            ->find();
        return $data;
    }

    /**
     * 获取用户登录信息并进行判断
     * @param mixed $object 平台token（平台id）
     * @param string $userName 输入的用户名
     * @param string $password 输入的用户密码
     * @return array
     * @throws ErpAuthException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginUser($object, string $userName, string $password): array
    {
        $user = userLogic::getInstance()->loginUser($userName);
        if (empty($user)) {
            throw new ErpAuthException('平台用户信息不存在', codeModel::NO_AUTH);
        }
        if (is_integer($object)) {
            $spread = spreadLogic::getInstance()->find($object);
        } else {
            $spread = spreadLogic::getInstance()->where('spread_token', $object)->find();
        }
        if (empty($spread)) {
            throw new ErpAuthException('平台信息不存在或已被删除', codeModel::AUTH_FAIL);
        }
        $spreadId = $spread['id'];
        if ($spread['state'] != spreadModel::STATE_OK) {
            throw new ErpAuthException('平台未启用或已被禁用', codeModel::AUTH_FAIL);
        }
        $spreadUser = $this->where(['spread_id' => $spreadId, 'user_id' => $user['id']])->find();
        if (empty($spreadUser)) {
            throw new ErpAuthException('平台用户信息不存在或已被禁用', codeModel::AUTH_FAIL);
        }
        if ($spreadUser['state'] != spreadUserModel::STATE_OK) {
            throw new ErpAuthException('用户信息未启用', codeModel::AUTH_FAIL);
        }
        if (!shellPassword($password, $spreadUser['password'])) {
            throw new ErpAuthException('用户密码错误', codeModel::AUTH_FAIL);
        }

        $spreadUser['spread'] = $spread;
        $spreadUser['user'] = $user;
        return [$spreadUser];
    }

    /**
     * 注销平台用户信息
     * @param int $spreadId 平台id
     * @param int $id 数据id
     * @param int $userId 用户id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id): bool
    {
        $spreadUser = $this->find($id);
        if (empty($spreadUser)) {
            recordError('平台管理员信息不存在或已被删除');
            return false;
        }
        if ($spreadUser['is_master'] == spreadUserModel::IS_MASTER) {
            recordError('平台管理员不可直接删除');
            return false;
        }
        if (!$spreadUser->delete()) {
            recordError($this->getLastSql());
            return false;
        }
        return true;
    }
}
