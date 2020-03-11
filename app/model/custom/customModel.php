<?php
declare (strict_types=1);

namespace app\model\custom;

use think\Model;

class customModel extends Model
{
    const STATE_OK = 1;             //状态：启用
    const STATE_BAN = 2;             //状态：禁用
}
