<?php
declare (strict_types = 1);

namespace app\model\supplier;

use think\Model;

class supplierModel extends Model
{
    const STATE_OK = 1;             //状态：1正常
    const STATE_BAN = 2;            //状态：2禁用
}
