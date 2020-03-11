<?php
declare (strict_types=1);

namespace app\model;

class commonModel
{
    const DEFAULT_PAGE = 1;         //默认页码数
    const DEFAULT_COUNT = 0;        //默认总页数
    const DEFAULT_SIZE = 15;        //默认每页显示条数
    const DEFAULT_LAST_PAGE = 1;    //默认最后一页
    const DEFAULT_TOTAL = 0;        //默认总条数

    /**
     * HashIds加盐
     * @var string
     */
    const HASH_ID_SALT = "xinErp2019010101";

    const STATE_OK = 1;             //状态：启用
    const STATE_BAN = 2;             //状态：禁用
}
