<?php
namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class Hello extends Command
{
    protected function configure()
    {
        $this->setName('hello')
            ->setDescription('Say Hello');
    }

    protected function execute(Input $input, Output $output)
    {
        $data = [
            'number' => mt_rand(100000, 999999),
            'create_time' => time()
        ];
        Db::name('test_crontab')->insert($data);
    }
}