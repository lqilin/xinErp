<?php
declare (strict_types=1);

namespace app\model;

use think\Model;

class authModel extends Model
{
    const AUTH_CUSTOM = 1;              //顾客
    const AUTH_SHOP = 2;                //店铺
    const AUTH_SPREAD = 4;              //平台
    const AUTH_ADMIN = 8;               //管理员
}
