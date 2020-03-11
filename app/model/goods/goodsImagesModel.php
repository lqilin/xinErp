<?php
declare (strict_types = 1);

namespace app\model\goods;

use think\Model;

class goodsImagesModel extends Model
{
    const IS_MASTER = 1;        //商品主图：1是
    const NO_MASTER = 2;        //商品主图：2不是
}
