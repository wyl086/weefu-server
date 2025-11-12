<?php


namespace app\common\command;


use app\admin\logic\AdminLogic;
use app\common\model\Admin;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Password extends Command
{
    /**
     * @notes
     * @author 令狐冲
     * @date 2021/11/24 10:56
     */
    protected function configure()
    {
        $this->setName('password')
            ->addArgument('password', Argument::OPTIONAL, "your name")
            ->setDescription('修改超级管理密码');
    }


    /**
     * @notes
     * @param Input $input
     * @param Output $output
     * @return int|void|null
     * @author 令狐冲
     * @date 2021/11/22 17:15
     */
    protected function execute(Input $input, Output $output)
    {
        $password = trim($input->getArgument('password'));
        if (empty($password)) {
            $output->error('请输入密码');
            return;
        }

        $adminInfo = Admin::where(['root' => 1])->findOrEmpty();

        if (empty($adminInfo)) {
            $output->info('超级管理员账号不存在');
        } else {
            AdminLogic::updatePassword($password, $adminInfo['id']);
            $output->info('超级管理修改密码成功！');
            $output->info('账号：' . $adminInfo['account']);
            $output->info('密码：' . $password);
        }
    }


}