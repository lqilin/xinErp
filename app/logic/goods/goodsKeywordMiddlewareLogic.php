<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 逻辑层
 * +----------------------------------------------------------------------
 * DATE 2020-02-03 16:57
 * @package app\logic\goods
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\goods;

use app\library\traits\erpAuthTrait;
use app\library\traits\erpModelEventTrait;
use app\library\traits\instanceTrait;
use app\logic\recycleBinLogic;
use app\logic\shop\shopLogic;
use app\logic\spread\spreadLogic;
use app\logic\user\userLogic;
use think\facade\Log;
use think\Model;
use think\model\Pivot;

class goodsKeywordMiddlewareLogic extends Pivot
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
     * 删除后置操作
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
}