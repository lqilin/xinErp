<?php

namespace app\library\traits;

use app\logic\admin\adminUserLogic;
use app\logic\custom\customLogic;
use app\logic\shop\shopUserLogic;
use app\logic\spread\spreadUserLogic;

trait erpModelEventTrait
{
    /**
     * @var array 关联信息
     */
    protected $withModel = [];

    /**
     * @var array 获取关联表信息
     */
    protected $relevance = [];

    /**
     * 后置操作刷新逻辑层数据
     * @var array
     */
    public $renovateLogic = [
        customLogic::class,
        spreadUserLogic::class,
        adminUserLogic::class,
        shopUserLogic::class,
    ];

    /**
     * 设置关联搜索的关联条件
     * @access public
     * @return void
     */
    public function setWithModel(): void
    {
        $with = request()->param('with', '');
        $this->withModel = array_intersect(explode(',', $with), $this->relevance);
    }

    /**
     * 获取关联搜索的关联条件
     * @access public
     * @return array
     */
    public function getWithModel()
    {
        return $this->withModel;
    }
}