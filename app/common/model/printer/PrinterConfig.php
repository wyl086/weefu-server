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
 * 打印机配置模型
 * Class PrinterConfig
 * @package app\common\model
 */
class PrinterConfig extends Models
{

    /**
     * @notes 根据打印机类型(易联云=1)获取配置
     * @param $type
     * @param $shop_id
     * @return array
     * @author 段誉
     * @date 2022/1/19 19:01
     */
    public static function getConfigByType($type, $shop_id)
    {
        return PrinterConfig::where(['type' => $type, 'shop_id' => $shop_id])
            ->field('client_id,client_secret')
            ->findOrEmpty()->toArray();
    }


    /**
     * @notes 根据id获取配置
     * @param $id
     * @param $shop_id
     * @return array
     * @author 段誉
     * @date 2022/1/20 9:56
     */
    public static function getConfigById($id, $shop_id)
    {
        return PrinterConfig::where(['id' => $id, 'shop_id' => $shop_id])
            ->field('client_id,client_secret')
            ->findOrEmpty()->toArray();
    }


}