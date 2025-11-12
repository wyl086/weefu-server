<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model\printer;


use app\common\basics\Models;

/**
 * 打印机模型
 * Class Printer
 * @package app\common\model
 */
class Printer extends Models
{

    // 设备类型描述
    public function getTypeDescAttr($value, $data)
    {
        return PrinterConfig::where(['id' => $data['config_id']])->value('name');
    }

    // 状态描述
    public function getStatusDescAttr($value, $data)
    {
        return $data['status'] ? '开启' : '关闭';
    }

    // 自动打印描述
    public function getAutoPrintDescAttr($value, $data)
    {
        return $data['auto_print'] ? '开启' : '关闭';
    }
}