<?php
declare (strict_types=1);

namespace app\command\order;

use app\logic\order\orderLogic;
use think\helper\Str;
use think\console\command\Make;
use think\console\Input;
use think\console\Output;
use think\facade\Env;

class Validate extends Make
{
    /**
     * 配置指令
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('order:validate')
            ->setDescription('Create an order module verifier');
    }

    /**
     * 执行指令
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));
        if ($name == 'all') {
            $this->executeAll($output);
        } else {
            $this->executeOne('order/' . $name, $output);
        }
    }

    /**
     * 创建全部订单验证器
     * @param Output $output
     */
    private function executeAll(Output $output)
    {
        $allType = config('erp.order_type');
        if (empty($allType)) {
            $output->writeln('<error>' . $this->type . ': the verifier rule does not exist.!</error>');
        }
        foreach ($allType as $type) {
            $name = 'order/' . $type['method'] . 'Validate';
            $classname = $this->getClassName($name);
            $pathname = $this->getPathName($classname);
            if (!$this->makeDocument($classname, $pathname, $output)) {
                continue;
            }
        }
        $output->writeln('<info>' . $this->type . ': all order validate created successfully.</info>');
    }

    /**
     * 创建单一的订单验证器
     * @param $name
     * @param Output $output
     */
    private function executeOne($name, Output $output)
    {
        $classname = $this->getClassName($name);
        $pathname = $this->getPathName($classname);
        $this->makeDocument($classname, $pathname, $output);
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
        $typeColumn = array_column($allType, null, 'method');
        $desc = '订单验证器';
        $typeName = '订单';
        $type = substr($class, 0, -strlen('validate'));
        if (!empty($typeColumn[$type])) {
            $desc = $typeColumn[$type]['title'] . '(' . $typeColumn[$type]['remark'] . ')';
            $typeName = $typeColumn[$type]['title'];
        }
        $timeNow = date('Y-m-d H:i', time());
        $erpName = Env::get('app.erp_name', 'shier-erp管理系统');
        return str_replace(['{%className%}', '{%actionSuffix%}', '{%namespace%}', '{%app_namespace%}', '{%desc%}', '{%timeNow%}', '{%erpName%}', '{%typeName%}'], [
            $class,
            $this->app->config->get('route.action_suffix'),
            $namespace,
            $this->app->getNamespace(),
            $desc,
            $timeNow,
            $erpName,
            $typeName
        ], $stub);
    }

    /**
     * @return string 获取配置目录
     */
    protected function getStub(): string
    {
        return app()->getBasePath() . 'command' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'order' . DIRECTORY_SEPARATOR . 'validate.stub';
    }

    /**
     * 获取命名空间
     * @param string $app
     * @return string
     */
    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\validate';
    }
}
