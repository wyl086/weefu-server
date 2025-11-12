<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\shop\validate\printer;

use app\common\basics\Validate;
use app\common\model\printer\Printer;
use app\common\model\printer\PrinterConfig;
use think\facade\Db;

class PrinterValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'config_id' => 'require|checkType',
        'name' => 'require',
        'machine_code' => 'require|unique:printer,machine_code^del^config_id',
        'private_key' => 'require',
        'print_number' => 'require',
    ];

    protected $message = [
        'id.require' => '请选择打印机',
        'config_id.require' => '请选择打印机类型',
        'name.require' => '请输入打印机名称',
        'machine_code.require' => '请输入终端号',
        'machine_code.unique' => '终端号重复',
        'private_key.require' => '请输入秘钥',
        'print_number.require' => '请输入打印联数',
    ];


    public function sceneAdd()
    {
        return $this->remove('id', true);
    }


    public function sceneDel()
    {
        return $this->only(['id']);
    }


    public function sceneConfig()
    {
        return $this->only(['id'])->append('id', 'checkPrinter');
    }



    // 验证类型
    protected function checkType($value, $rule, $data)
    {
        $type = PrinterConfig::where(['id' => $value, 'shop_id' => $data['shop_id'], 'del' => 0])->findOrEmpty();
        if ($type->isEmpty()) {
            return '打印机配置错误';
        }
        if (empty($type['client_id']) || empty($type['client_secret'])) {
            return '请先设置' . $type['name'] . '的配置';
        }
        return true;
    }

    // 验证打印机
    protected function checkPrinter($value, $rule, $data)
    {
        $printer = Printer::where(['id' => $value, 'shop_id' => $data['shop_id']])->find();
        if (!$printer || !$printer['machine_code']) {
            return '打印机配置错误';
        }

        $type = PrinterConfig::where(['id' => $printer['config_id']])->find();

        if (!$type) {
            return '打印配置错误';
        }
        if (!$type['client_id'] || !$type['client_secret']) {
            return '请先设置' . $type['name'] . '的配置';
        }
        return true;
    }
}