<?php
declare (strict_types = 1);

namespace app\model\user;

use think\Model;

/**
 * @mixin think\Model
 */
class userModel extends Model
{
    const SEX_MAN = 1;              //性别：男
    const SEX_WOMAN = 2;            //性别：女
    const SEX_UNKNOWN = 3;          //性别：未知
}
