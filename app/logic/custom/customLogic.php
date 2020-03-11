<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 客户逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-07 10:47
 * @package app\logic\custom
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\custom;

use app\model\user\userModel;
use app\logic\baseLogic;
use app\logic\spread\spreadLogic;
use app\logic\user\userLogic;
use app\model\commonModel;
use app\model\spread\spreadModel;
use app\model\spread\spreadUserModel;
use app\library\traits\jwtAuthTrait;
use think\db\Query;
use think\model\concern\SoftDelete;
use think\model\relation\BelongsTo;

class customLogic extends baseLogic
{
    use SoftDelete;
    use jwtAuthTrait;

    /**
     * @var string 数据表名
     */
    protected $name = 'custom';

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
        'custom_category',
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
     * @return belongsTo 关联用户信息表
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(userLogic::class, 'user_id');
    }

    /**
     * @return BelongsTo 关联客户分类表信息
     */
    public function customCategory(): BelongsTo
    {
        return $this->belongsTo(customCategoryLogic::class, 'custom_category_id');
    }

    /**
     * @return belongsTo 关联平台信息
     */
    public function spread(): belongsTo
    {
        return $this->belongsTo(spreadLogic::class, 'id', 'spread_id');
    }

    /**
     * 用户搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchUserIdAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('c.user_id', $value);
        }
    }

    /**
     * 时间搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr(Query $query, $value, $data)
    {
        if (!empty($value[0]) && !empty($value[1])) {
            $query->whereBetweenTime('custom_logic.create_time', $value[0], $value[1]);
        } else {
            if (!empty($value[0])) {
                $query->whereTime('custom_logic.create_time', '>=', $value[0]);
            } elseif (!empty($value[1])) {
                $query->whereTime('custom_logic.create_time', '<=', $value[1]);
            }
        }
    }

    /**
     * 客户分类搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchCustomCategoryIdAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('custom_category_id', $value);
        }
    }

    /**
     * 用户信息搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchKeywordAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            if (isMobile($value)) {
                $query->where('user.phone', $value);
            } elseif (isEmail($value)) {
                $query->where('user.email', $value);
            } else {
                $query->whereLike('user.user_nickname', "%" . $value . "%");
            }
        }
    }

    /**
     * 获取客户列表信息
     * @param     $param
     * @param int $page
     * @param int $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCustomList($param, int $page = commonModel::DEFAULT_PAGE, int $size = commonModel::DEFAULT_SIZE)
    {
        $result = [
            'page' => $page,
            'count' => $size,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'data' => [],
        ];
        $this->spreadId = $param['spread_id'] ?? 0;
        $search = ['keyword', 'create_time', 'state', 'user_id', 'custom_category_id'];
        $data = $this
            ->withJoin(['user'])
            ->with(['custom_category'])
            ->withSearch($search, $param)
            ->visible(['user' => ['user_nickname', 'mobile', 'user_email'], 'custom_category' => ['category_name']])
            ->paginate($size);
        $data->each(function ($item) {
            unset($item['password']);
            return $item;
        });
        $result['data'] = $data->items();
        $result['count'] = $data->count();
        $result['last_page'] = $data->lastPage();
        return $result;
    }

    /**
     * 根据token获取用户信息
     * @param int   $spreadId
     * @param int   $userId
     * @param array $hidden
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTokenUserInfo(int $spreadId, int $userId, array $hidden = [])
    {
        $info = $this
            ->with(['spread', 'user'])
            ->where('spread_id', $spreadId)
            ->where('user_id', $userId)
            ->cache(true)
            ->hidden($hidden)
            ->find();
        return $info;
    }

    /**
     * 保存客户信息
     * @param $param
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveCustom($param): bool
    {
        $user = userLogic::getInstance()->where('mobile', $param['phone'])->find();
        $saveData = [
            'custom_category_id' => $param['custom_category_id'] ?: 0,
            'password' => !empty($param['password']) ? password_hash($param['password'], PASSWORD_BCRYPT) : '',
            'remark' => $param['remark'] ?? '',
        ];
        $this->startTrans();
        if (!empty($user)) {
            $custom = $this->where(['user_id' => $user['id'], 'spread_id' => $this->spreadId])->find();
            $saveData['user_id'] = $user['id'];
            if (!empty($custom)) {
                $saveData['id'] = $custom['id'];
                if (!empty($custom['password']) && empty($param['password'])) {
                    $saveData['password'] = $custom['password'];
                }
            }
        } else {
            $userData = [
                'sex' => !empty($param['sex']) ? $param['sex'] : userModel::SEX_UNKNOWN,
                'birthday' => !empty($param['birthday']) ? strtotime($param['birthday']) : time(),
                'mobile' => $param['phone'],
            ];
            if (!empty($userData)) {
                if (!$userId = userLogic::getInstance()->insertGetId($userData)) {
                    $this->rollback();
                    recordError('保存用户信息失败，请稍后再试');
                    return false;
                }
                $saveData['user_id'] = $userId;
            }
        }
        if (empty($saveData['user_id'])) {
            $this->rollback();
            recordError('未获取到用户信息');
            return false;
        }
        if (!$this->save($saveData)) {
            $this->rollback();
            recordError('操作失败，请稍后再试');
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 客户登录信息
     * @param int    $spreadId
     * @param string $userName
     * @param string $password
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginUser(int $spreadId, string $userName, string $password)
    {
        $user = userLogic::getInstance()->loginUser($userName);
        if (empty($user)) {
            recordError('平台用户信息不存在');
            return false;
        }
        $spread = spreadLogic::getInstance()->find($spreadId);
        if (empty($spread)) {
            recordError('平台信息不存在或已被禁用');
            return false;
        }
        if ($spread['state'] != spreadModel::STATE_OK) {
            recordError('平台未启用或已被禁用');
            return false;
        }
        $custom = $this->where(['spread_id' => $spreadId, 'user_id' => $user['id']])->find();
        if (empty($custom)) {
            recordError('平台客户信息不存在或已被禁用');
            return false;
        }
        if ($custom['state'] != spreadUserModel::STATE_OK) {
            recordError('平台客户信息未启用');
            return false;
        }
        if (!shellPassword($password, $custom['password'])) {
            recordError('用户密码错误');
            return false;
        }

        $custom['spread'] = $spread;
        $custom['user'] = $user;
        return [$custom];
    }

    /**
     * 删除客户信息数据
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id)
    {
        $custom = $this->find($id);
        if (empty($custom)) {
            recordError('客户信息不存在或已被删除');
            return false;
        }
        if (!$custom->delete()) {
            recordError('客户信息注销失败，请稍候再试');
            return false;
        }
        return true;
    }
}