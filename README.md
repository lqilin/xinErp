xinErp
===============
> 十贰-ERP

> 运行环境要求PHP7.1+。

> 采用thinkPHP6内核

* 采用`PHP7`强类型（严格模式）
* 支持更多的`PSR`规范
* 原生多应用支持
* 更强大和易用的查询
* 全新的事件系统
* 模型事件和数据库事件统一纳入事件系统
* 模板引擎分离出核心
* 内部功能中间件化
* SESSION/Cookie机制改进
* 对Swoole以及协程支持改进
* 对IDE更加友好
* 统一和精简大量用法

## 安装
- Git 拉取代码
~~~
git clone git@github.com:dashingunique/xinErp.git
~~~
- composer 安装拓展
~~~
composer install
~~~
- 如果需要更新拓展包
~~~
composer update
~~~
## 拓展包
> xinErp 所有拓展
~~~
        "topthink/framework": "6.0.*-dev",
        "topthink/think-orm": "2.0.*-dev",
        "topthink/think-view": "^1.0",
        "topthink/think-queue": "^3.0",
        "topthink/think-migration": "^3.0",
        "ext-json": "*",
        "ext-bcmath": "*",
        "ext-mbstring": "*",
        "topthink/think-captcha": "^3.0",
        "topthink/think-image": "^1.0",
        "symfony/var-dumper":"^4.2",
        "overtrue/wechat": "~4.0",
        "xaboy/form-builder": "^1.2",
        "firebase/php-jwt": "^5.0",
        "phpoffice/phpexcel": "^1.8",
        "aliyuncs/oss-sdk-php": "^2.3",
        "qcloud/cos-sdk-v5": "^1.3",
        "qiniu/php-sdk": "^7.2",
        "workerman/workerman": "^3.5",
        "workerman/channel": "^1.0",
        "spatie/macroable": "^1.0",
        "dh2y/think-qrcode": "^2.0",
        "topthink/think-annotation": "^1.0",
        "topthink/think-trace": "^1.1",
        "topthink/think-multi-app": "^1.0",
        "hashids/hashids": "^4.0",
        "yunwuxin/think-notification": "3.0.*",
        "topthink/think-throttle": "dev-master",
        "casbin/think-authz": "dev-master",
        "yupoxiong/region": "dev-master"
~~~
##架构
- 自动生成目录：
~~~
    自动生成controller文件：
    php think make:controller userController
    
    自动生成model文件：
    php think make:model userModel
    
    ...
    
~~~
- xinErp自定义命令
~~~
    xinErp新增自动生成logic逻辑处理层，此文件用于处理数据处理逻辑相关：
    自动生成命令：php think make:logic userLogic 
    
    格式放在app/command/stubs的logic.stub文件中，格式可自定义
~~~
- 架构：
#### xinErp采用thinkPHP6为内核，继承thinkPHP6的优良性能的同时更加注重代码的简单明了于注释清晰，
力求让每个人都能看懂xinErp，路由方面采用think-annotation，文档可参考：[tp6完全开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

#### xinErp集成多端接口，仅需一步让不同接口赋予全部权限：
~~~
controller 继承 app\controller\Base

在方法内调用：
checkAuthority方法
栗子：

    public function deleteStorehouse(Request $request)
    {
        $this->checkAuthority($request->loginTerminal, AuthModel::AUTH_SPREAD + AuthModel::AUTH_ADMIN);
        $id = $request->param('id/d', 0);
        if (empty($id)) {
            apiError('参数缺失');
        }
        if (!StorehouseLogic::getInstance()->deleteInfo($request->spreadId, $id, $request->userId)) {
            apiError(getLastError());
        }
        apiSuccess('操作成功');
    }
    
    $request->loginTerminal 为当前登录的用户 ，类型有四种：客户、店铺、平台、总后台
    权限已经在app\model\AuthModel 中定义，若此接口多端通用：AuthModel::AUTH_SPREAD + AuthModel::AUTH_ADMIN
    表示平台、总后台用户登录可获取该接口数据
~~~
## thinkphp6开发文档

[完全开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

## 版权信息

xinErp 遵循Apache2开源协议发布，并提供免费使用。

All rights reserved。

xinErp® 商标和著作权所有者为”张大宝的程序人生（1107842285@qq.com）“所有

更多细节参阅 [LICENSE.txt](LICENSE.txt)
