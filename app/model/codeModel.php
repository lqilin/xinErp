<?php
declare (strict_types=1);

namespace app\model;

class codeModel
{
    //逻辑处理类
    const OK = 1000;                 //逻辑成功返回
    const ERROR = 1001;              //逻辑失败返回

    //Auth 验证类
    const NO_AUTH = 2001;            //身份验证错误或登录信息已过期
    const AUTH_FAIL = 2002;          //身份验证失败
    const UNKNOWN_USER = 2003;       //身份信息不存在

    //资源请求类
    const OVER = 4001;               //服务器拒绝请求
    const NOT_FOUND = 4004;          //未找到相应资源
}
