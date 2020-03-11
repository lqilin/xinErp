<?php


namespace app\library\traits;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use think\facade\Env;
use UnexpectedValueException;

trait jwtAuthTrait
{
    /**
     * @param string $type
     * @param array $params
     * @return array
     */
    public function getToken(string $type, array $params = []): array
    {
        $id = $this->{$this->getPk()};
        $host = app()->request->host();
        $time = time();

        $params += [
            'iss' => $host,
            'aud' => $host,
            'iat' => $time,
            'nbf' => $time,
            'ip' => app()->request->ip(),
            'exp' => strtotime('+ 2hour'),
        ];
        $params['jti'] = compact('id', 'type');
        $token = JWT::encode($params, Env::get('jwt.secret', '10086'));

        return compact('token', 'params');
    }

    /**
     * 根据token获取用户相关信息
     * @param string $jwt
     * @return array
     *
     * @throws UnexpectedValueException     Provided JWT was invalid
     * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed
     * @throws BeforeValidException         Provided JWT is trying to be used before it's eligible as defined by 'nbf'
     * @throws BeforeValidException         Provided JWT is trying to be used before it's been created as defined by 'iat'
     * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim
     *
     */
    public static function parseToken(string $jwt): array
    {
        JWT::$leeway = Env::get('jwt.ttl', '120');
        $data = JWT::decode($jwt, Env::get('jwt.secret', '10086'), array('HS256'));
        $logic = new self();
        return [$logic->getTokenUserInfo($data->spread_id, $data->user_id, ['password']), $data->jti->type, $data->device_type];
    }
}