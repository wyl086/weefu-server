<?php
namespace app\admin\validate\system;

use think\Validate;
use Cron\CronExpression;

class CrontabValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'name' => 'require',
        'type' => 'require|in:1,2',
        'command' => 'require',
        'status' => 'require|in:1,2',
        'expression' => 'expression',
    ];

    protected $message = [
        'expression.expression'=>'定时任务规则设置错误',
    ];

    /**
     * 添加
     */
    public function sceneAdd()
    {
        $this->remove('id');
    }

    /**
     * 命令验证
     * @param $password
     * @param $other
     * @param $data
     * @return bool|mixed
     */
    protected function expression($expression, $other, $data)
    {
        if ($data['type'] == 2) {
            return true;
        }
        if (empty($expression)) {
            return '定时任务的规则不能为空';
        }
        if (CronExpression::isValidExpression($expression) === false) {
            return false;
        }
        $cron_expression = CronExpression::factory($expression);
        try {
            $cron_expression->getMultipleRunDates(1);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}