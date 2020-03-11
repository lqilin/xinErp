<?php
declare (strict_types=1);

namespace app\controller;


class Error
{
    public function __call($method, $args)
    {
        $host = request()->host();
        if (strstr($host, '127.0.0.1')) {
            return view('../view/index/come.html');
        }
        return view('../view/index/404.html');
    }
}
