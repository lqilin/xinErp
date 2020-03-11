<?php
declare (strict_types=1);

namespace app\command\order;

use app\logic\order\orderLogic;
use think\helper\Str;
use think\console\command\Make;
use think\console\Input;
use think\console\Output;
use think\facade\Env;

class GroupLogic extends Make
{
    /**
     * 配置指令
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('order:groupLogic')
            ->setDescription('Create an order grouping logic layer');
    }

    /**
     * 执行指令
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $allType = config('erp.order_type');
        if (empty($allType)) {
            $output->writeln('<error>' . $this->type . ': the group does not exist.!</error>');
        }
        foreach ($allType as $order) {
            $name = 'order/group/' . $order['method'] . 'Logic';
            $classname = $this->getClassName($name);
            $pathname = $this->getPathName($classname);
            if (!$this->makeDocument($classname, $pathname, $output)) {
                continue;
            }
        }
        $output->writeln('<info>' . $this->type . ': all group logic file created successfully.</info>');
    }

    /**
     * @param $classname
     * @param $pathname
     * @param Output $output
     * @return bool
     */
    private function makeDocument($classname, $pathname, Output $output)
    {
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
        $allType = config('erp.order_type');
        $typeColumn = array_column($allType, 'title', 'method');
        $title = !empty($typeColumn[substr($class, 0, -strlen('logic'))]) ? $typeColumn[substr($class, 0, -strlen('logic'))] : '订单';
        $timeNow = date('Y-m-d H:i', time());
        $erpName = Env::get('app.erp_name', 'shier-erp管理系统');
        return str_replace(['{%className%}', '{%actionSuffix%}', '{%namespace%}', '{%app_namespace%}', '{%timeNow%}', '{%erpName%}', '{%title%}'], [
            $class,
            $this->app->config->get('route.action_suffix'),
            $namespace,
            $this->app->getNamespace(),
            $timeNow,
            $erpName,
            $title
        ], $stub);
    }

    /**
     * @return string 获取配置目录
     */
    protected function getStub(): string
    {
        return app()->getBasePath() . 'command' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'order' . DIRECTORY_SEPARATOR . 'logic.stub';
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
