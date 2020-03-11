<?php
declare (strict_types=1);

namespace app\model\spread;

use think\Model;

class spreadModel extends Model
{
    const STATE_OK = 1;                 //状态：启用
    const STATE_FORBIDDEN = 2;          //状态：禁用
}
