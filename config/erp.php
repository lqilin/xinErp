<?php

use app\logic\admin\adminUserLogic;
use app\logic\custom\customLogic;
use app\logic\order\group\orderTypeRetailLogic;
use app\logic\order\group\orderTypeSaleGatheringLogic;
use app\logic\order\group\orderTypeSaleLogic;
use app\logic\order\group\orderTypeSaleRefundLogic;
use app\logic\order\group\orderTypeStockInLogic;
use app\logic\order\group\orderTypeStockLogic;
use app\logic\order\group\orderTypeStockPayLogic;
use app\logic\order\group\orderTypeStockRefundLogic;
use app\logic\order\group\orderTypeStorehouseAllocationLogic;
use app\logic\order\group\orderTypeStorehouseCheckLogic;
use app\logic\order\group\orderTypeStorehouseCostChangeLogic;
use app\logic\order\group\orderTypeStorehouseDismountingLogic;
use app\logic\order\group\orderTypeStorehouseOtherOutLogic;
use app\logic\order\group\orderTypeStorehouseOtherStockLogic;
use app\logic\order\group\orderTypeStorehouseStockChangeLogic;
use app\logic\order\group\orderTypeWholesaleLogic;
use app\logic\shop\shopUserLogic;
use app\logic\spread\spreadUserLogic;
use app\model\authModel;
use app\model\order\orderModel;
use app\validate\order\orderTypeRetailValidate;
use app\validate\order\orderTypeSaleGatheringValidate;
use app\validate\order\orderTypeSaleRefundValidate;
use app\validate\order\orderTypeSaleValidate;
use app\validate\order\orderTypeStockInValidate;
use app\validate\order\orderTypeStockPayValidate;
use app\validate\order\orderTypeStockRefundValidate;
use app\validate\order\orderTypeStockValidate;
use app\validate\order\orderTypeStorehouseAllocationValidate;
use app\validate\order\orderTypeStorehouseCheckValidate;
use app\validate\order\orderTypeStorehouseCostChangeValidate;
use app\validate\order\orderTypeStorehouseDismountingValidate;
use app\validate\order\orderTypeStorehouseOtherOutValidate;
use app\validate\order\orderTypeStorehouseOtherStockValidate;
use app\validate\order\orderTypeStorehouseStockChangeValidate;
use app\validate\order\orderTypeWholesaleValidate;

return [
    //权限用户对应的逻辑层
    'auth_logic' => [
        authModel::AUTH_CUSTOM => customLogic::class,
        authModel::AUTH_SHOP => shopUserLogic::class,
        authModel::AUTH_SPREAD => spreadUserLogic::class,
        authModel::AUTH_ADMIN => adminUserLogic::class,
    ],
    //订单对应的每种不同订单对应的逻辑层
    'order_type' => [
        //进货业务
        orderModel::ORDER_TYPE_STOCK => [
            'type' => orderModel::ORDER_TYPE_STOCK,
            'method' => 'orderTypeStock',
            'class' => orderTypeStockLogic::class,
            'validate' => orderTypeStockValidate::class,
            'group' => 'stock',
            'title' => '进货单',
            'remark' => '向供应商提前预定，商品还未入库'
        ],
        orderModel::ORDER_TYPE_STOCK_IN => [
            'type' => orderModel::ORDER_TYPE_STOCK_IN,
            'method' => 'orderTypeStockIn',
            'class' => orderTypeStockInLogic::class,
            'validate' => orderTypeStockInValidate::class,
            'group' => 'stock',
            'title' => '进货入库单',
            'remark' => '从供应商处进货，过账后库存、金额将变化'
        ],
        orderModel::ORDER_TYPE_STOCK_REFUND => [
            'type' => orderModel::ORDER_TYPE_STOCK_REFUND,
            'method' => 'orderTypeStockRefund',
            'class' => orderTypeStockRefundLogic::class,
            'validate' => orderTypeStockRefundValidate::class,
            'group' => 'stock',
            'title' => '进货退货单',
            'remark' => '把商品退还给供应商'
        ],
        orderModel::ORDER_TYPE_STOCK_PAY => [
            'type' => orderModel::ORDER_TYPE_STOCK_PAY,
            'method' => 'orderTypeStockPay',
            'class' => orderTypeStockPayLogic::class,
            'validate' => orderTypeStockPayValidate::class,
            'group' => 'stock',
            'title' => '进货付款单',
            'remark' => '向供应商预付贷款、付款、结算欠款等'
        ],

        //销售业务
        orderModel::ORDER_TYPE_SALE => [
            'type' => orderModel::ORDER_TYPE_SALE,
            'method' => 'orderTypeSale',
            'class' => orderTypeSaleLogic::class,
            'validate' => orderTypeSaleValidate::class,
            'group' => 'sale',
            'title' => '销售订单',
            'remark' => '向客户提前预定商品，商品还未出库'
        ],
        orderModel::ORDER_TYPE_WHOLESALE => [
            'type' => orderModel::ORDER_TYPE_WHOLESALE,
            'method' => 'orderTypeWholesale',
            'class' => orderTypeWholesaleLogic::class,
            'validate' => orderTypeWholesaleValidate::class,
            'group' => 'sale',
            'title' => '批发单',
            'remark' => '向批发客户销售、过账后库存、金额等将变化'
        ],
        orderModel::ORDER_TYPE_RETAIL => [
            'type' => orderModel::ORDER_TYPE_RETAIL,
            'method' => 'orderTypeRetail',
            'class' => orderTypeRetailLogic::class,
            'validate' => orderTypeRetailValidate::class,
            'group' => 'sale',
            'title' => '零售单',
            'remark' => '向零售客户销售、过账后库存、金额等将变化'
        ],
        orderModel::ORDER_TYPE_SALE_REFUND => [
            'type' => orderModel::ORDER_TYPE_SALE_REFUND,
            'method' => 'orderTypeSaleRefund',
            'class' => orderTypeSaleRefundLogic::class,
            'validate' => orderTypeSaleRefundValidate::class,
            'group' => 'sale',
            'title' => '销售退货单',
            'remark' => '客户把商品退给我'
        ],
        orderModel::ORDER_TYPE_SALE_GATHERING => [
            'type' => orderModel::ORDER_TYPE_SALE_GATHERING,
            'method' => 'orderTypeSaleGathering',
            'class' => orderTypeSaleGatheringLogic::class,
            'validate' => orderTypeSaleGatheringValidate::class,
            'group' => 'sale',
            'title' => '销售收款单',
            'remark' => '向客户预收贷款、收款、结算欠款等'
        ],

        //库存业务
        orderModel::ORDER_TYPE_STOREHOUSE_CHECK => [
            'type' => orderModel::ORDER_TYPE_STOREHOUSE_CHECK,
            'method' => 'orderTypeStorehouseCheck',
            'class' => orderTypeStorehouseCheckLogic::class,
            'validate' => orderTypeStorehouseCheckValidate::class,
            'group' => 'storehouse',
            'title' => '库存盘点单',
            'remark' => '盘点商品的实际库存，且可做盈亏处理'
        ],
        orderModel::ORDER_TYPE_STOREHOUSE_ALLOCATION => [
            'type' => orderModel::ORDER_TYPE_STOREHOUSE_ALLOCATION,
            'method' => 'orderTypeStorehouseAllocation',
            'class' => orderTypeStorehouseAllocationLogic::class,
            'validate' => orderTypeStorehouseAllocationValidate::class,
            'group' => 'storehouse',
            'title' => '调拨单',
            'remark' => '把商品从A仓库调到B仓库'
        ],
        orderModel::ORDER_TYPE_STOREHOUSE_DISMOUNTING => [
            'type' => orderModel::ORDER_TYPE_STOREHOUSE_DISMOUNTING,
            'method' => 'orderTypeStorehouseDismounting',
            'class' => orderTypeStorehouseDismountingLogic::class,
            'validate' => orderTypeStorehouseDismountingValidate::class,
            'group' => 'storehouse',
            'title' => '拆装单',
            'remark' => '商品之间的组合拆分'
        ],
        orderModel::ORDER_TYPE_STOREHOUSE_OTHER_STOCK => [
            'type' => orderModel::ORDER_TYPE_STOREHOUSE_OTHER_STOCK,
            'method' => 'orderTypeStorehouseOtherStock',
            'class' => orderTypeStorehouseOtherStockLogic::class,
            'validate' => orderTypeStorehouseOtherStockValidate::class,
            'group' => 'storehouse',
            'title' => '其他入库单',
            'remark' => '除正常正常进货外的进货商品，如商家的赠品等'
        ],
        orderModel::ORDER_TYPE_STOREHOUSE_OTHER_OUT => [
            'type' => orderModel::ORDER_TYPE_STOREHOUSE_OTHER_OUT,
            'method' => 'orderTypeStorehouseOtherOut',
            'class' => orderTypeStorehouseOtherOutLogic::class,
            'validate' => orderTypeStorehouseOtherOutValidate::class,
            'group' => 'storehouse',
            'title' => '其他出库单',
            'remark' => '除正常销售外的其他出库，如送给客户赠品等'
        ],
        orderModel::ORDER_TYPE_STOREHOUSE_COST_CHANGE => [
            'type' => orderModel::ORDER_TYPE_STOREHOUSE_COST_CHANGE,
            'method' => 'orderTypeStorehouseCostChange',
            'class' => orderTypeStorehouseCostChangeLogic::class,
            'validate' => orderTypeStorehouseCostChangeValidate::class,
            'group' => 'storehouse',
            'title' => '成本调整单',
            'remark' => '修改、调整店仓内商品的成本价'
        ],
        orderModel::ORDER_TYPE_STOREHOUSE_STOCK_CHANGE => [
            'type' => orderModel::ORDER_TYPE_STOREHOUSE_STOCK_CHANGE,
            'method' => 'orderTypeStorehouseStockChange',
            'class' => orderTypeStorehouseStockChangeLogic::class,
            'validate' => orderTypeStorehouseStockChangeValidate::class,
            'group' => 'storehouse',
            'title' => '库存调整单',
            'remark' => '修改、调整店仓内商品的库存数量'
        ],
    ],
];