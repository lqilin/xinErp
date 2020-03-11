<?php
namespace app\library\traits;

trait erpSimpleModelTrait
{
    public function getRow($param)
    {
        $db = $this->db();
    }

    private function _dealWhere(array $params = [])
    {
        $db = $this->db();
        if (isset($params['_fields_'])) {
            $db->field($params['_fields_']);
            unset($params['_fields_']);
        }

        if (isset($params['_limit_'])) {
            if (is_array($params['_limit_']) && isset($params['_limit_'][0]) && $params['_limit_'][1]) {
                $db->limit($params['_limit_'][0], $params['_limit_'][1]);
            } else {
                $db->limit($params['_limit_']);
            }
            unset($params['_limit_']);
        }

        if (isset($params['_order_'])) {
            $db->order($params['_order_']);
            unset($params['_order_']);
        }

        if (isset($params['_orderRaw_'])) {
            $db->orderRaw($params['_orderRaw_']);
            unset($params['_orderRaw_']);
        }

        if (isset($params['_or_'])) {
            $db->whereOr($params['_or_']);
            unset($params['_or_']);
        }

        if (isset($params['_readMaster_'])) {
            $db->master();
            unset($params['_readMaster_']);
        }

        if (isset($params['_lock_'])) {
            $db->lock($params['_lock_']);
            unset($params['_lock_']);
        }

        if (isset($params['_group_'])) {
            $db->group($params['_group_']);
            unset($params['_group_']);
        }

        if (!empty($params)) {
            $db->where($params);
        }

        return $db;
    }
}