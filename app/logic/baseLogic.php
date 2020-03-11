<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 基础逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-06 16:37
 * @package app\logic\user
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic;

use app\library\traits\erpAuthTrait;
use app\library\traits\erpModelEventTrait;
use app\logic\shop\shopLogic;
use app\logic\spread\spreadLogic;
use app\logic\user\userLogic;
use app\model\commonModel;
use app\library\traits\instanceTrait;
use think\db\Query;
use think\facade\Log;
use think\Model;

class baseLogic extends Model
{
    use instanceTrait;                  //获取模型的单例

    use erpAuthTrait;                   //获取搜全信息

    use erpModelEventTrait;             //获取模型关联的数据


    // 定义全局的查询范围
    protected $globalScope = ['spread'];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        if (isset(request()->authInfo)) $this->setAuthInfo(request()->authInfo);
        if (isset(request()->userInfo)) $this->setUserInfo(request()->userInfo);
        if (isset(request()->spreadInfo)) $this->setSpreadInfo(request()->spreadInfo);
        if (isset(request()->shopInfo)) $this->setShopInfo(request()->shopInfo);
        if (isset(request()->spreadId)) $this->setSpreadId(request()->spreadId);
        if (isset(request()->userId)) $this->setUserId(request()->userId);
        if (isset(request()->shopId)) $this->setShopId(request()->shopId);
        if (isset(request()->fromId)) $this->setFromId(request()->fromId);
        if (isset(request()->loginTerminal)) $this->setLoginTerminal(request()->loginTerminal);
        if (isset(request()->isLogin)) $this->setIsLogin();
    }

    /**
     * @param mixed $query 查询平台范围
     */
    public function scopeSpread($query)
    {
        if (!empty($this->spreadId)) {
            $query->where('spread_id', $this->spreadId);
        }
    }

    /**
     * 更新后置操作
     * @param Model $model
     */
    public static function onAfterUpdate(Model $model)
    {
        if ($model instanceof userLogic) {
            $model->setUserInfo($model);
        }
        if ($model instanceof spreadLogic) {
            $model->setSpreadInfo($model);
        }
        if ($model instanceof shopLogic) {
            $model->setShopInfo($model);
        }
        if (isset($model->renovateLogic)) {
            foreach ($model->renovateLogic as $logic) {
                if ($model instanceof $logic) {
                    $model->setAuthInfo($model);
                }
            }
        }
    }

    /**
     * 删除前置操作
     * @param Model $model
     * @return mixed|void
     */
    public static function onBeforeDelete(Model $model)
    {
        //搜索数据之前先把数据存放到回收站中
        $saveData = [
            'object_id' => $model[$model->pk] ?: 0,
            'table_name' => $model->name,
        ];
        if (recycleBinLogic::getInstance()->saveData($saveData)) {
            Log::notice('OnBeforeDelete ');
        }
    }

    /**
     *  删除后置操作
     * @param Model $model
     */
    public static function onAfterDelete($model)
    {
        if ($model instanceof userLogic) {
            $model->setUserInfo($model);
        }
        if ($model instanceof spreadLogic) {
            $model->setSpreadInfo($model);
        }
        if ($model instanceof shopLogic) {
            $model->setShopInfo($model);
        }
        if (isset($model->renovateLogic)) {
            foreach ($model->renovateLogic as $logic) {
                if ($model instanceof $logic) {
                    $model->setAuthInfo($model);
                }
            }
        }
    }

    /**
     * 新增前置操作
     * @param Model $model
     * @return mixed|void
     */
    public static function onBeforeInsert(Model $model)
    {
        if (!empty($model->autoObject)) {
            $model->appendData($model->autoObject);
        }
    }

    /**
     * ID信息搜索器
     * @param Query $query
     * @param mixed $value
     * @param array $data
     */
    public function searchIdAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('id', $value);
        }
    }

    /**
     * 时间搜索器
     * @param Query $query
     * @param mixed $value
     * @param array $data
     */
    public function searchCreateTimeAttr(Query $query, $value, $data)
    {
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            $query->whereBetweenTime('create_time', $data['start_time'], $data['end_time']);
        } else {
            if (!empty($data['start_time'])) {
                $query->whereTime('create_time', '>=', $data['start_time']);
            } elseif (!empty($data['end_time'])) {
                $query->whereTime('create_time', '<=', $data['end_time']);
            }
        }
    }

    /**
     * 状态搜索器
     * @param Query $query
     * @param mixed $value
     * @param array $data
     */
    public function searchStateAttr(Query $query, $value, $data)
    {
        if (in_array($value, [commonModel::STATE_OK, commonModel::STATE_BAN])) {
            $query->where('state', $value);
        }
    }

    /**
     * 省份搜索器
     * @param Query $query
     * @param mixed $value
     * @param array $data
     */
    public function searchProvinceAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('province', $value);
        }
    }

    /**
     * 城市搜索器
     * @param Query $query
     * @param mixed $value
     * @param array $data
     */
    public function searchCityAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('city', $value);
        }
    }

    /**
     * 用户ID搜索器
     * @param Query $query
     * @param mixed $value
     * @param array $data
     */
    public function searchUserIdAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('user_id', $value);
        }
    }

    /**
     * 店铺ID搜索器
     * @param Query $query
     * @param mixed $value
     * @param array $data
     */
    public function searchShopIdAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('shop_id', $value);
        }
    }

    /**
     * 区/县搜索器
     * @param Query $query
     * @param mixed $value
     * @param array $data
     */
    public function searchDistrictAttr(Query $query, $value, $data)
    {
        if (!empty($value)) {
            $query->where('district', $value);
        }
    }
}