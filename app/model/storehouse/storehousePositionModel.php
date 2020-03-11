<?php
declare (strict_types = 1);

namespace app\model\storehouse;

use think\Model;

class storehousePositionModel extends Model
{
    const IS_DEFAULT = 1;       //是否默认仓位 1默认 2不是默认
    const NO_DEFAULT = 2;       //是否默认仓位 1默认 2不是默认

    const STATE_OK = 1;         //状态：1正常
    const STATE_BAN = 2;        //状态：2禁用
}
