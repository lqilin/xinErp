<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 店铺逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-09 13:22
 * @package app\logic\shop
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\shop;

use app\logic\baseLogic;
use app\logic\regionLogic;
use app\model\commonModel;
use think\db\Query;
use think\model\concern\SoftDelete;

class shopLogic extends baseLogic
{
    use SoftDelete;

    /**
     * @var string 数据表名
     */
    protected $name = 'shop';

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
     * 关键词搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchKeywordAttr(Query $query, $value)
    {
        if (!empty($value)) {
            if (isMobile($value)) {
                $query->where('contact_tel', $value);
            } elseif (isEmail($value)) {
                $query->where('contact_email', $value);
            } else {
                $query->whereLike('shop_name|address', "%" . $value . "%");
            }
        }
    }

    /**
     * 平台token搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchSpreadTokenAttr(Query $query, $value)
    {
        if (!empty($value)) {
            $query->where('spread_id', decodeStr($value));
        }
    }

    /**
     * 获取店铺列表
     * @param array $param
     * @param int $page
     * @param int $size
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getShopList(array $param, int $page, int $size)
    {
        $result = [
            'page' => $page,
            'count' => 0,
            'last_page' => commonModel::DEFAULT_LAST_PAGE,
            'data' => [],
        ];
        $search = ['spread_token', 'shop_type', 'shop_type_child', 'keyword', 'province', 'city', 'district', 'state'];
        $data = $this->withSearch($search, $param)->order('sort DESC, create_time DESC')->page($page, $size)->select();
        $count = $this->withSearch($search, $param)->cache(true)->count();
        $data->each(function ($item) {
            $regionData = regionLogic::getInstance()->withoutGlobalScope()->where(['id' => [$item['province'], $item['city'], $item['district']]])->select()->toArray();
            $regionColumn = array_column($regionData, 'name', 'id');
            $item['province_name'] = $regionColumn[$item['province']] ?? '';
            $item['city_name'] = $regionColumn[$item['city']] ?? '';
            $item['district_name'] = $regionColumn[$item['district']] ?? '';
            $item['shop_token'] = encodeId($item['id'], $this->name);
            return $item;
        });
        $result['count'] = $count;
        $result['last_page'] = ceil($count / $size) > commonModel::DEFAULT_LAST_PAGE ? ceil($count / $size) : commonModel::DEFAULT_LAST_PAGE;
        $result['data'] = $data;
        return $result;
    }

    /**
     * 保存店铺信息
     * @param $param
     * @return bool
     */
    public function saveShopInfo($param): bool
    {
        $saveData = [
            'user_id' => $param['user_id'],
            'spread_id' => $param['spread_id'],
            'shop_name' => $param['shop_name'] ?: '未知店铺',
            'shop_type' => $param['shop_type'] ?: 0,
            'shop_type_child' => $param['shop_type_child'] ?: 0,
            'contact_tel' => $param['contact_tel'] ?: '',
            'contact_email' => $param['contact_email'] ?: '',
            'province' => $param['province'] ?: '',
            'city' => $param['city'] ?: '',
            'district' => $param['district'] ?: '',
            'address' => $param['address'] ?: '',
            'map_lon' => $param['map_lon'] ?: '',
            'map_lat' => $param['map_lat'] ?: '',
            'logo_path' => $param['logo_path'] ?: '',
            'service' => $param['service'] ?: '',
            'remark' => $param['remark'] ?: '',
        ];
        if (!empty($param['id'])) {
            $saveData['id'] = $param['id'];
        }
        if (!$this->save($saveData)) {
            recordError('保存失败请稍后再试');
            return false;
        }
        return true;
    }

    /**
     * 删除店铺信息
     * @param int $id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteInfo(int $id)
    {
        $shopInfo = $this->find($id);
        if (empty($shopInfo)) {
            recordError('店铺信息不存在或已被删除');
            return false;
        }
        if (!$shopInfo->delete()) {
            recordError('删除店铺信息失败，请稍候再试');
            return false;
        }
        return true;
    }
}