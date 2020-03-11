<?php
declare (strict_types = 1);

namespace app\validate\mall;

use think\Validate;

class mallGoodsTypeValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'name|类型名称' => 'require|max:50',
    ];

}
