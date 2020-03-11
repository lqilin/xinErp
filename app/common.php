<?php
// 应用公共文件
use app\model\codeModel;
use app\model\commonModel;
use Hashids\Hashids;
use think\exception\HttpResponseException;
use think\facade\Db;
use think\Response;

if (!function_exists('apiReturn')) {
    /**
     * 返回提示信息
     * @access protected
     * @param mixed $code   错误码
     * @param mixed $msg    提示信息,若要指定错误码,可以传数组,格式为['code'=>您的错误码,'msg'=>'您的错误消息']
     * @param mixed $data   返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    function apiReturn(int $code = codeModel::OK, string $msg = '操作成功', array $data = [], array $header = [])
    {
        if (is_array($msg)) {
            $code = $msg['code'];
            $msg = $msg['msg'];
        }
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];

        $type = 'json';
        $header['Access-Control-Allow-Origin'] = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }
}

if (!function_exists('apiError')) {
    /**
     * 返回错误信息
     * @access protected
     * @param mixed $msg    提示信息,若要指定错误码,可以传数组,格式为['code'=>您的错误码,'msg'=>'您的错误消息']
     * @param mixed $data   返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    function apiError($msg = '获取失败', array $data = [], array $header = [])
    {
        $result = [
            'code' => codeModel::ERROR,
            'msg' => $msg,
            'data' => $data,
        ];

        $type = 'json';
        $header['Access-Control-Allow-Origin'] = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }
}

if (!function_exists('apiSuccess')) {
    /**
     * 返回成功信息
     * @access protected
     * @param mixed $msg    提示信息,若要指定错误码,可以传数组,格式为['code'=>您的错误码,'msg'=>'您的错误消息']
     * @param mixed $data   返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    function apiSuccess($msg = '获取成功', array $data = [], array $header = [])
    {
        $result = [
            'code' => codeModel::OK,
            'msg' => $msg,
            'data' => $data,
        ];

        $type = 'json';
        $header['Access-Control-Allow-Origin'] = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }
}

if (!function_exists('apiPaginate')) {
    /**
     * 分页
     * @time 2019年12月06日
     * @param array $list 分页数据内容
     * @return void
     */
    function apiPaginate(array $list)
    {
        $result = [
            'code' => codeModel::OK,
            'msg' => '获取成功',
            'data' => [
                'page' => $list['page'] ?: commonModel::DEFAULT_PAGE,
                'count' => $list['count'] ?: commonModel::DEFAULT_TOTAL,
                'last_page' => $list['last_page'] ?: commonModel::DEFAULT_LAST_PAGE,
                'data' => $list['data'] ?: [],
            ],
        ];

        $type = 'json';
        $header['Access-Control-Allow-Origin'] = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }
}


if (!function_exists('recordError')) {
    /**
     * 设置错误信息
     * @access protected
     * @param mixed $str 错误信息
     * @return bool
     */
    function recordError($str)
    {
        global $errorInfo;
        $errorInfo = $str;
        return true;
    }
}

if (!function_exists('getLastError')) {
    /**
     * 获取全局錯誤信息
     * @return string
     */
    function getLastError()
    {
        global $errorInfo;
        if (!empty($errorInfo)) {
            return $errorInfo;
        } else {
            return "";
        }
    }
}

if (!function_exists('exception')) {
    /**
     * 抛出异常处理
     * @param string  $msg       异常消息
     * @param integer $code      异常代码 默认为0
     * @param string  $exception 异常类
     * @throws Exception
     */
    function exception($msg, $code = 0, $exception = '')
    {
        $e = $exception ?: '\think\Exception';
        throw new $e($msg, $code);
    }
}

if (!function_exists('filterEmoji')) {

    // 过滤掉emoji表情
    function filterEmoji($str)
    {
        $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        return $str;
    }
}


if (!function_exists('strReplace')) {
    /**
     * @param string $string 需要替换的字符串
     * @param int    $start  开始的保留几位
     * @param int    $end    最后保留几位
     * @return string
     */
    function strReplace($string, $start, $end)
    {
        $strlen = mb_strlen($string, 'UTF-8');//获取字符串长度
        $firstStr = mb_substr($string, 0, $start, 'UTF-8');//获取第一位
        $lastStr = mb_substr($string, -1, $end, 'UTF-8');//获取最后一位
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($string, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;

    }
}


if (!function_exists('sensitive_words_filter')) {

    /**
     * 敏感词过滤
     * @param string $str
     * @return string
     */
    function sensitive_words_filter(string $str): string
    {
        if (!$str) return '';
        $file = app()->getAppPath() . 'public/static/plug/censorwords/CensorWords';
        $words = file($file);
        foreach ($words as $word) {
            $word = str_replace(["\r\n", "\r", "\n", "/", "<", ">", "=", " "], '', $word);
            if (!$word) continue;

            $ret = preg_match("/$word/", $str, $match);
            if ($ret) {
                return $match[0];
            }
        }
        return '';
    }
}

if (!function_exists('shellPassword')) {
    /**
     * 密码加密方法
     * @param string $password 输入的密码
     * @param string $hash     储存的hash密码
     * @return bool
     */
    function shellPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

if (!function_exists('agentFrom')) {
    /**
     * 获取用户访问的设备来源
     * @return string
     */
    function agentFrom(): string
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if (strpos($agent, 'windows nt')) {
            $platform = 'windows';
        } elseif (strpos($agent, 'macintosh')) {
            $platform = 'mac';
        } elseif (strpos($agent, 'ipod')) {
            $platform = 'iPod';
        } elseif (strpos($agent, 'ipad')) {
            $platform = 'iPad';
        } elseif (strpos($agent, 'iphone')) {
            $platform = 'iphone';
        } elseif (strpos($agent, 'android')) {
            $platform = 'android';
        } elseif (strpos($agent, 'MicroMessenger')) {
            $platform = 'wx';
        } elseif (strpos($agent, 'AlipayClient')) {
            $platform = 'ali';
        } elseif (strpos($agent, 'miniprogram')) {
            $platform = 'wxApp';
        } else if (preg_match('/win/i', $agent)) {
            $platform = 'pc';
        } else {
            $platform = 'other';
        }

        return $platform;
    }
}

if (!function_exists('encodeId')) {
    /**
     * hashIds加盐数据id
     * @param int    $id
     * @param string $salt
     * @param int    $length
     * @return string
     */
    function encodeId(int $id, string $salt = commonModel::HASH_ID_SALT, int $length = 8): string
    {
        $hash = new Hashids($salt, $length);
        return $hash->encode($id);
    }
}

if (!function_exists('decodeStr')) {
    /**
     * 解密字符串获取id
     * @param string $str
     * @param string $salt
     * @param int    $length
     * @return int
     */
    function decodeStr(string $str, string $salt = commonModel::HASH_ID_SALT, int $length = 8): int
    {
        $hash = new Hashids($salt, $length);
        $res = $hash->decode($str);

        if (empty($res)) {
            return 0;
        }

        return $res = count($res) > 1 ? $res : $res[0];
    }
}

if (!function_exists('orderSn')) {
    /**
     * 获取唯一的订单编号
     * @param string $prefix
     * @return string
     */
    function orderSn(string $prefix = ''): string
    {
        return $prefix . (strtotime(date('YmdHis', time()))) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
    }
}

if (!function_exists('friendlyDate')) {
    /**
     * 友好的时间显示
     * @param int    $sTime 待显示的时间
     * @param string $type  类型. normal | mohu | full | ymd | other
     * @param string $alt   已失效
     * @return string
     */
    function friendlyDate($sTime, $type = 'normal')
    {
        if (!$sTime) {
            return '';
        }
        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime = time();
        $dTime = $cTime - $sTime;
        $dDay = intval(date("z", $cTime)) - intval(date("z", $sTime));
        //$dDay     =   intval($dTime/3600/24);
        $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
        //normal：n秒前，n分钟前，n小时前，日期
        if ($type == 'normal') {
            if ($dTime < 60) {
                if ($dTime < 10) {
                    return '刚刚';    //by yangjs
                } else {
                    return intval(floor($dTime / 10) * 10) . "秒前";
                }
            } elseif ($dTime < 3600) {
                return intval($dTime / 60) . "分钟前";
                //今天的数据.年份相同.日期相同.
            } elseif ($dYear == 0 && $dDay == 0) {
                //return intval($dTime/3600)."小时前";
                return '今天' . date('H:i', $sTime);
            } elseif ($dYear == 0) {
                return date("m月d日 H:i", $sTime);
            } else {
                return date("Y-m-d H:i", $sTime);
            }
        } elseif ($type == 'mohu') {
            if ($dTime < 60) {
                return $dTime . "秒前";
            } elseif ($dTime < 3600) {
                return intval($dTime / 60) . "分钟前";
            } elseif ($dTime >= 3600 && $dDay == 0) {
                return intval($dTime / 3600) . "小时前";
            } elseif ($dDay > 0 && $dDay <= 7) {
                return intval($dDay) . "天前";
            } elseif ($dDay > 7 && $dDay <= 30) {
                return intval($dDay / 7) . '周前';
            } elseif ($dDay > 30) {
                return intval($dDay / 30) . '个月前';
            }
            //full: Y-m-d , H:i:s
        } elseif ($type == 'full') {
            return date("Y-m-d , H:i:s", $sTime);
        } elseif ($type == 'ymd') {
            return date("Y-m-d", $sTime);
        } else {
            if ($dTime < 60) {
                return $dTime . "秒前";
            } elseif ($dTime < 3600) {
                return intval($dTime / 60) . "分钟前";
            } elseif ($dTime >= 3600 && $dDay == 0) {
                return intval($dTime / 3600) . "小时前";
            } elseif ($dYear == 0) {
                return date("Y-m-d H:i:s", $sTime);
            } else {
                return date("Y-m-d H:i:s", $sTime);
            }
        }
        return '';
    }
}

if (!function_exists('makeUploadPath')) {

    /**
     * 上传路径转化,默认路径
     * @param      $path
     * @param int  $type
     * @param bool $force
     * @return string
     * @throws Exception
     */
    function makeUploadPath($path, int $type = 2, bool $force = false)
    {
        $path = DS . ltrim(rtrim($path));
        switch ($type) {
            case 1:
                $path .= DS . date('Y');
                break;
            case 2:
                $path .= DS . date('Y') . DS . date('m');
                break;
            case 3:
                $path .= DS . date('Y') . DS . date('m') . DS . date('d');
                break;
        }
        try {
            if (is_dir(app()->getRootPath() . 'public' . DS . 'uploads' . $path) == true || mkdir(app()->getRootPath() . 'public' . DS . 'uploads' . $path, 0777, true) == true) {
                return trim(str_replace(DS, '/', $path), '.');
            } else return '';
        } catch (\Exception $e) {
            if ($force) {
                throw new \Exception($e->getMessage());
            }
            return '无法创建文件夹，请检查您的上传目录权限：' . app()->getRootPath() . 'public' . DS . 'uploads' . DS . 'attach' . DS;
        }

    }
}

if (!function_exists('isMobile')) {
    /**
     * 验证手机号是否正确
     * @param string $mobile
     * @return bool
     */
    function isMobile($mobile)
    {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^1[3,4,5,7,8,9]{1}[\d]{9}$#', $mobile) ? true : false;
    }
}

if (!function_exists('isEmail')) {
    /**
     * 正则表达式验证email格式
     * @param string $str 所要验证的邮箱地址
     * @return bool
     */
    function isEmail($str)
    {
        if (!$str) {
            return false;
        }
        return preg_match('#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $str) ? true : false;
    }
}

if (!function_exists('isTel')) {
    /**
     * 验证输入的电话号码是否是固话或者手机号
     * @param string $phone
     * @return bool
     */
    function isTel(string $phone): bool
    {
        // 验证联系电话
        $isMob = "/^1[34578]{1}\d{9}$/";
        $isTel = "/^([0-9]{3,4}-)?[0-9]{7,8}$/";
        if (!empty($phone)) {
            if (!preg_match($isMob, $phone) && !preg_match($isTel, $phone)) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('loginTerminal')) {
    function loginTerminal(): int
    {
        $header = request()->header();
        return $header['login_terminal'] ?? 0;
    }
}

if (!function_exists('getTableComment')) {
    /**
     * 获取数据表注释
     * @param string $name
     * @return string
     */
    function getTableComment(string $name): string
    {
        $table = Db::query("show table status like '" . env('database.prefix', 'erp_') . $name . "'");
        return !empty($table[0]['Comment']) ? $table[0]['Comment'] : '';
    }
}