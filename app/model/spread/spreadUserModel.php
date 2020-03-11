<?php
declare (strict_types=1);

namespace app\model\spread;

use think\Model;

class spreadUserModel extends Model
{
    const STATE_OK = 1;                 //状态：启用
    const STATE_NO_AUTH = 2;            //状态：未认证
    const STATE_FORBIDDEN = 3;          //状态：禁用

    const IS_MASTER = 1;                //是否是管理员：1是
    const NO_MASTER = 2;                //是否是管理员：2不是
}
