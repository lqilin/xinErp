<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 库存缓存
 * +----------------------------------------------------------------------
 * project shier-erp管理系统
 */

namespace app\cache;

use app\library\traits\instanceTrait;
use think\Cache;

class StorehouseCache extends Cache
{
    use instanceTrait;
    /**
     * @var string 缓存标识
     */
    const PREFIX = 'STOREHOUSE';

}
