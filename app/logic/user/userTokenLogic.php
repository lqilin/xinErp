<?php
/**
 * | Author: 张大宝的程序人生
 * +----------------------------------------------------------------------
 * | Description: 用户token逻辑层
 * +----------------------------------------------------------------------
 * DATE 2019-12-06 16:37
 * @package app\logic\user
 * project shier-erp管理系统
 */
declare (strict_types=1);

namespace app\logic\user;

use app\library\exceptions\ErpAuthException;
use app\library\exceptions\ErpException;
use app\logic\baseLogic;
use app\model\authModel;
use app\library\traits\jwtAuthTrait;
use app\model\codeModel;

class userTokenLogic extends baseLogic
{
    use jwtAuthTrait;
    /**
     * @var string 数据表名
     */
    protected $name = 'user_token';

    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var bool 是否开启字段时间戳字段
     */
    protected $autoWriteTimestamp = true;

    /**
     * 更新token信息
     * @param $oldToken
     * @param $data
     * @param $loginTerminal
     * @param $deviceType
     * @return userTokenLogic
     * @throws ErpAuthException
     * @throws ErpException
     */
    public function updateToken($oldToken, $data, $loginTerminal, $deviceType)
    {
        if (empty($data)) {
            throw new ErpAuthException('登录信息错误，请稍后再试', codeModel::NO_AUTH);
        }
        if (!in_array($loginTerminal, [authModel::AUTH_CUSTOM, authModel::AUTH_SHOP, authModel::AUTH_SPREAD, authModel::AUTH_ADMIN])) {
            throw new ErpException('来源错误，请稍后再试', codeModel::UNKNOWN_USER);
        }
        $param = [
            'spread_id' => !empty($data['spread_id']) ? $data['spread_id'] : 0,
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : 0,
            'shop_id' => !empty($data['shop_id']) ? $data['shop_id'] : 0,
            'login_terminal' => $loginTerminal,
            'device_type' => $deviceType,
        ];
        $tokenInfo = $this->getToken('JWT', $param);
        return self::update([
            'id' => $oldToken['id'],
            'token' => $tokenInfo['token'],
            'expire_time' => $tokenInfo['params']['exp'],
        ]);
    }

    /**
     * 创建token信息
     * @param $data
     * @param $loginTerminal
     * @param $deviceType
     * @return userTokenLogic|\think\Model
     * @throws ErpAuthException
     * @throws ErpException
     */
    public function createToken($data, $loginTerminal, $deviceType)
    {
        if (empty($data)) {
            throw new ErpAuthException('登录信息错误，请稍后再试', codeModel::NO_AUTH);
        }
        if (!in_array($loginTerminal, [authModel::AUTH_CUSTOM, authModel::AUTH_SHOP, authModel::AUTH_SPREAD, authModel::AUTH_ADMIN])) {
            throw new ErpException('来源错误，请稍后再试', codeModel::UNKNOWN_USER);
        }
        $param = [
            'spread_id' => !empty($data['spread_id']) ? $data['spread_id'] : 0,
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : 0,
            'shop_id' => !empty($data['shop_id']) ? $data['shop_id'] : 0,
            'login_terminal' => $loginTerminal,
            'device_type' => $deviceType,
        ];
        $tokenInfo = $this->getToken('JWT', $param);
        return self::create([
            'role_id' => !empty($data['id']) ? $data['id'] : 0,
            'token' => $tokenInfo['token'],
            'expire_time' => $tokenInfo['params']['exp'],
            'device_type' => $deviceType,
            'ip' => request()->ip(),
            'login_terminal' => $loginTerminal,
        ]);
    }
}
