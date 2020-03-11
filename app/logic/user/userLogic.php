<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 用户信息逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-07 09:46
 * @package app\logic\user
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\user;

use app\logic\baseLogic;
use think\model\concern\SoftDelete;

class userLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'user';

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
     * @var array 设置全局搜索
     */
    protected $globalScope = [];

    /**
     * 通过输入的用户名信息获取登录信息
     * @param string $userName
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginUser(string $userName)
    {
        if (isMobile($userName)) {
            return $this->where('mobile', $userName)->find();
        } elseif (isEmail($userName)) {
            return $this->where('user_email', $userName)->find();
        } else {
            return $this->where('user_login', $userName)->find();
        }
    }

    /**
     * 注销用户数据信息
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id)
    {
        $user = $this->find($id);
        if (empty($user)) {
            recordError('用户基础数据不存在或已被删除');
            return false;
        }
        if (!$user->delete()) {
            recordError('注销用户数据失败，请稍候再试');
            return false;
        }
        return true;
    }
}