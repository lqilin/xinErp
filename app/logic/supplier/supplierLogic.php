<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-03-02 22:40
 * @package app\logic\supplier
 * project shier-erp管理系统
 */
declare (strict_types = 1);

namespace app\logic\supplier;

use app\logic\BaseLogic;
use think\model\concern\SoftDelete;

class supplierLogic extends BaseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'supplier';

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
     * 保存供应商信息
     * @param array $param
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveSupplier(array $param)
    {
        if (!empty($param['id'])) {
            return $this->editSupplier($param);
        }else {
            return $this->addSupplier($param);
        }
    }

    /**
     * 编辑供应商信息
     * @param array $param
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function editSupplier(array $param)
    {
        $info = $this->find($param['id']);
        if (empty($info)) {
            recordError('供应商信息不存在或已被删除');
            return false;
        }
        $info->data($param);
        if (!$info->save()) {
            recordError('供应商信息保存失败，请稍后再试');
            return false;
        }
        return true;
    }

    /**
     * 添加供应商信息
     * @param array $param
     * @return bool
     */
    private function addSupplier(array $param)
    {
        if (!self::create($param)) {
            recordError('供应商信息保存失败，请稍后再试');
            return false;
        }
        return true;
    }

     /**
     * 删除数据信息
     * @param int $id 数据ID
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id): bool
    {
        $info = $this->find($id);
        if (empty($info)) {
            recordError('信息不存在或已被删除');
            return false;
        }
        if (!$info->delete()) {
            recordError('删除信息失败，请稍候再试');
            return false;
        }
        return true;
    }
}