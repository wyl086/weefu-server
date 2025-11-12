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

namespace app\shop\logic\free_shipping;

use app\common\basics\Logic;
use app\common\server\ConfigServer;
use think\facade\Db;

class FreeShippingLogic extends Logic
{
    public static function index($params)
    {
        Db::startTrans();
        try {
            // 保存设置
            $config = Db::name('free_shipping_config')->where([
                'shop_id' => $params['shop_id'],
                'del' => 0
            ])->findOrEmpty();
            if (empty($config)) {
                // 无则添加
                Db::name('free_shipping_config')->save([
                    'shop_id' => $params['shop_id'],
                    'status' => $params['status'],
                    'goods_type' => $params['goods_type'],
                    'free_rule' => $params['free_rule'],
                    'create_time' => time(),
                    'del' => 0,
                ]);
            } else {
                // 有则更新
                Db::name('free_shipping_config')->where([
                    'shop_id' => $params['shop_id'],
                    'del' => 0
                ])->update([
                    'status' => $params['status'],
                    'goods_type' => $params['goods_type'],
                    'free_rule' => $params['free_rule'],
                    'update_time' => time()
                ]);
            }
            // 先清除旧数据
            Db::name('free_shipping_region')->where([
                'shop_id' => $params['shop_id'],
                'del' => 0
            ])->update(['del' => 1]);
            // 保存活动区域
            $data = [];
            $time = time();
            $del = 0;
            foreach($params['region'] as $key => $value) {
                if($params['order_amount'][$key] < 0) {
                    throw new \Exception('订单金额不能小于0');
                }
                $data[] = ['shop_id' => $params['shop_id'], 'region' => $value, 'order_amount' => $params['order_amount'][$key], 'create_time' => $time, 'del' => $del];
            }

            Db::name('free_shipping_region')->insertAll($data);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function getData($shopId)
    {
        $config = Db::name('free_shipping_config')
            ->field('status,goods_type,free_rule')
            ->where([
            'shop_id' => $shopId,
            'del' => 0,
        ])->findOrEmpty();
        if(empty($config)) {
            // 默认值
            $config = [
                'status' => 0,
                'goods_type' => 1,
                'free_rule' => 1,
            ];
        }
        $regionArr = Db::name('free_shipping_region')->field('region,order_amount')
            ->where([
                'shop_id' => $shopId,
                'del' => 0,
            ])->select()->toArray();

        $regions = Db::name('dev_region')->column('name', 'id');

        foreach ($regionArr as &$item) {
            $item['region_name'] = '';

            if ($item['region'] == 'all'){
                $item['region_name'] = '全国地区默认规则';
                continue;
            }

            $region = explode(',', $item['region']);

            foreach ($region as $v) {
                if (isset($regions[$v])) {
                    $item['region_name'] .= $regions[$v] . ',';
                }
            }
            $item['region_name'] = rtrim($item['region_name'], ',');
        }

        return [
            'config' => $config,
            'region' => $regionArr,
        ];
    }
}