<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 缓存
 * +----------------------------------------------------------------------
 * project shier-erp管理系统
 */

namespace app\cache;

use app\library\traits\instanceTrait;
use think\Cache;

class SpreadClearFormCache extends Cache
{
    use instanceTrait;

    /**
     * @var string 缓存标识
     */
    const PREFIX = 'SPREAD_CLEAR_FORM';
}