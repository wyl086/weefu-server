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

namespace app\shop\logic\printer;

use app\common\basics\Logic;
use app\common\model\printer\PrinterConfig;

/**
 * 打印设置逻辑层
 * Class ConfigLogic
 * @package app\admin\logic\printer
 */
class ConfigLogic extends Logic
{

    /**
     * @notes 打印设置列表
     * @param $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/1/19 17:20
     */
    public static function lists($shop_id)
    {
        self::createDefaultConfig($shop_id);

        $lists = PrinterConfig::where(['del' => 0, 'shop_id' => $shop_id])->select();

        return ['lists' => $lists];
    }



    /**
     * @notes 创建当前门店默认打印机配置
     * @param $shop_id
     * @author 段誉
     * @date 2022/1/19 17:20
     */
    public static function createDefaultConfig($shop_id)
    {
        $config = PrinterConfig::where(['shop_id' => $shop_id, 'del' => 0])->findOrEmpty();

        if ($config->isEmpty()) {
            PrinterConfig::create([
                'name' => '易联云',
                'shop_id' => $shop_id,
                'client_id' => 0,
                'client_secret' => 0,
                'type' => 1,
                'update_time' => time(),
            ]);
        }
    }


    /**
     * @notes 配置详情
     * @param $id
     * @param $shop_id
     * @return array|\think\Model
     * @author 段誉
     * @date 2022/1/19 17:21
     */
    public static function getDetail($id, $shop_id)
    {
        return PrinterConfig::where(['id' => $id, 'shop_id' => $shop_id])->findOrEmpty();
    }



    /**
     * @notes 设置配置
     * @param $post
     * @param $shop_id
     * @return PrinterConfig
     * @author 段誉
     * @date 2022/1/19 17:21
     */
    public static function editConfig($post, $shop_id)
    {
        $post['status'] = isset($post['status']) && $post['status'] == 'on' ? 1 : 0;
        if ($post['status']) {
            PrinterConfig::where(['status' => 1, 'shop_id' => $shop_id])->update(['status' => 0]);
        }
        $update_data = [
            'client_id' => $post['client_id'],
            'client_secret' => $post['client_secret'],
            'update_time' => time(),
            'status' => $post['status'],
        ];
        return PrinterConfig::where(['id' => $post['id'], 'shop_id' => $shop_id])->update($update_data);
    }

}