<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 平台结算方式逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-06 16:37
 * @package app\logic\spread
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\spread;

use app\logic\baseLogic;
use app\model\commonModel;
use think\model\concern\SoftDelete;

class spreadClearFormLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'spread_clear_form';

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
     * 保存平台结算信息
     * @param $param = [
     *      'id' => 1,              //费用信息id
     *      'spread_id' => 1,       //平台id
     *      'clearing_form' => '线上结算',//结算方式
     * ]
     * @return bool
     */
    public function saveClear($param): bool
    {
        $saveData = [
            'clearing_form' => $param['clearing_form'],
            'spread_id' => $param['spread_id'],
        ];
        if (!empty($param['id'])) {
            $saveData['id'] = $param['id'];
        }

        if (!$this->save($saveData)) {
            recordError('操作失败，请稍后再试');
            return false;
        }
        return true;
    }

    /**
     * 获取平台结算方式
     * @param array $param
     * @param int $page
     * @param int $size
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getSpreadClearForm(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'count' => $size,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'data' => [],
        ];
        $this->spreadId = $param['spread_id'] ?: 0;
        $data = $this
            ->withSearch(['id', 'state', 'keyword', 'create_time'], $param)
            ->hidden(['spread_id'])
            ->paginate($size);

        $result['count'] = $data->count();
        $result['last_page'] = $data->lastPage();
        $result['data'] = $data->items();

        return $result;
    }

    /**
     * 删除数据信息
     * @param int $id 数据id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id)
    {
        $info = $this->find($id);
        if (empty($info)) {
            recordError('结算类型信息不存在或已被删除');
            return false;
        }
        if (!$info->delete()) {
            recordError('删除失败，请稍后再试');
            return false;
        }
        return true;
    }
}
