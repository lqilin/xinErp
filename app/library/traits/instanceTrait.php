<?php
/**
 * | Author: paradise <1107842285@qq.com>
 * +----------------------------------------------------------------------
 * | Description: 邮箱信息控制器
 * +----------------------------------------------------------------------
 * Class ${NAME}
 */

namespace app\library\traits;

trait instanceTrait
{
    /**
     * 单例模式申明
     * instances
     * @var array
     */
    private static $instances;


    /**
     *获取相对应的单例
     * get instance
     * @param mixed $param
     * @return static
     */
    public static function getInstance($param = [])
    {
        $className = get_called_class();

        if (empty(self::$instances[$className])) {
            self::$instances[$className] = new static($param);
        }

        return self::$instances[$className];
    }
}