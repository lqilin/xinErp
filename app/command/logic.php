<?php
declare (strict_types=1);

namespace app\command;

use EasyWeChat\Kernel\Support\Str;
use think\console\command\Make;
use think\console\Input;
use think\console\Output;
use think\facade\Env;

class logic extends Make
{
    /**
     * 配置指令
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('make:logic')
            ->setDescription('Create a new logic class');
    }

    /**
     * 执行指令
     * @param Input $input
     * @param Output $output
     * @return bool|int|null
     */
    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));

        $classname = $this->getClassName($name);

        $pathname = $this->getPathName($classname);

        if (is_file($pathname)) {
            $output->writeln('<error>' . $this->type . ':' . $classname . ' already exists!</error>');
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $this->buildClass($classname));

        $output->writeln('<info>' . $this->type . ':' . $classname . ' created successfully.</info>');
    }

    /**
     * 创建目录
     * @param string $name
     * @return mixed
     */
    protected function buildClass(string $name)
    {
        $stub = file_get_contents($this->getStub());

        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);
        $tableName = strtolower(Str::snake(substr($class, 0, -strlen('logic'))));
        $timeNow = date('Y-m-d H:i', time());
        $erpName = Env::get('app.erp_name', 'shier-erp管理系统');
        return str_replace(['{%className%}', '{%actionSuffix%}', '{%namespace%}', '{%app_namespace%}', '{%tableName%}', '{%timeNow%}', '{%erpName%}'], [
            $class,
            $this->app->config->get('route.action_suffix'),
            $namespace,
            $this->app->getNamespace(),
            $tableName,
            $timeNow,
            $erpName
        ], $stub);
    }

    /**
     * @return string 获取配置目录
     */
    protected function getStub(): string
    {
        return app()->getBasePath() . 'command' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'logic.stub';
    }

    /**
     * 获取命名空间
     * @param string $app
     * @return string
     */
    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\logic';
    }
}
