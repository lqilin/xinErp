<?php
declare (strict_types=1);

namespace app\model\spread;

use think\Model;

class spreadClearFormModel extends Model
{
    const STATE_OK = 1;             //状态：启用
    const STATE_BAN = 2;             //状态：禁用
}
