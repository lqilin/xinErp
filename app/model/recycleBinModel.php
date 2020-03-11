<?php

namespace app\model;

class recycleBinModel
{
    const STATE_DAFT = 1;       //状态：1垃圾箱中（30天后自动清除）
    const STATE_RECOVER = 2;    //状态：2已恢复
}
