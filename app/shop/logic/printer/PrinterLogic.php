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
use app\common\model\printer\Printer;
use app\common\model\printer\PrinterConfig;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use app\common\server\YlyPrinter;
use think\facade\Cache;
use think\facade\Db;

/**
 * 打印机管理逻辑层
 * Class PrinterLogic
 * @package app\admin\logic\printer
 */
class PrinterLogic extends Logic
{
    /**
     * @notes 打印机列表
     * @param $get
     * @param $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/1/20 10:54
     */
    public static function lists($get, $shop_id)
    {
        $where[] = ['p.del', '=', 0];
        $where[] = ['p.shop_id', '=', $shop_id];

        $lists = Printer::alias('p')
            ->where($where)
            ->append(['status_desc', 'auto_print_desc', 'type_desc'])
            ->page($get['page'], $get['limit'])
            ->order('p.id desc')
            ->select()
            ->toArray();

        return ['count' => 0, 'lists' => $lists];
    }


    /**
     * @notes 添加打印机
     * @param $post
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2022/1/20 10:53
     */
    public static function add($post, $shop_id)
    {
        Db::startTrans();
        try {
            Printer::create([
                'shop_id' => $shop_id,
                'config_id' => $post['config_id'],
                'name' => $post['name'],
                'machine_code' => $post['machine_code'],
                'private_key' => $post['private_key'],
                'print_number' => $post['print_number'],
                'auto_print' => $post['auto_print'],
                'status' => $post['status'],
                'create_time' => time(),
                'update_time' => time(),
            ]);

            // 打印机配置
            $config = PrinterConfig::getConfigById($post['config_id'], $shop_id);
            $yly_print = new YlyPrinter($config['client_id'], $config['client_secret'], $shop_id);

            //调用易联云添加打印机
            $yly_print->addPrinter($post['machine_code'], $post['private_key'], $post['name']);

            Db::commit();
            return true;

        } catch (\Exception $e) {
            $msg = json_decode($e->getMessage(), true);
            if ($msg && isset($msg['error'])) {
                return '易联云：' . $msg['error_description'];
            }

            if (18 === $e->getCode()) {
                Cache::rm('yly_access_token' . $shop_id);
                Cache::rm('yly_refresh_token' . $shop_id);
            }
            Db::rollback();
            return '易联云：' . $e->getMessage();
        }
    }


    /**
     * @notes 编辑打印机
     * @param $post
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2022/1/20 10:53
     */
    public static function edit($post, $shop_id)
    {
        Db::startTrans();
        try {
            $now = time();
            $data = [
                'name' => $post['name'],
                'print_number' => $post['print_number'],
                'status' => $post['status'],
                'auto_print' => $post['auto_print'],
                'update_time' => $now,
            ];
            Printer::where(['id' => $post['id']])->update($data);

            //调用易联云，更新打印机
            $config = PrinterConfig::getConfigById($post['config_id'], $shop_id);
            $yly_print = new YlyPrinter($config['client_id'], $config['client_secret'], $shop_id);

            //调用易联云添加打印机
            $yly_print->addPrinter($post['machine_code'], $post['private_key'], $post['name']);

            Db::commit();
            return true;

        } catch (\Exception $e) {

            $msg = json_decode($e->getMessage(), true);
            if ($msg && isset($msg['error'])) {
                return '易联云：' . $msg['error_description'];
            }
            if (18 === $e->getCode()) {
                Cache::rm('yly_access_token'. $shop_id);
                Cache::rm('yly_refresh_token'. $shop_id);
            }
            Db::rollback();
            return '易联云：' . $e->getMessage();
        }
    }


    /**
     * @notes 删除打印机
     * @param $id
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2022/1/20 9:58
     */
    public static function del($id, $shop_id)
    {
        Db::startTrans();
        try {
            Printer::where(['id' => $id, 'shop_id' => $shop_id])->update(['del' => 1, 'update_time' => time()]);
            $printer = Printer::where(['id' => $id, 'shop_id' => $shop_id])->findOrEmpty();
            $config = PrinterConfig::getConfigById($printer['config_id'], $shop_id);

            //调用易联云接口，删除打印机
            $yly_print = new YlyPrinter($config['client_id'], $config['client_secret'], $shop_id);
            $yly_print->deletePrinter($printer['machine_code']);

            Db::commit();
            return true;

        } catch (\Exception $e) {

            $msg = json_decode($e->getMessage(), true);
            if ($msg && isset($msg['error'])) {
                return '易联云：' . $msg['error_description'];
            }
            if (18 === $e->getCode()) {
                Cache::rm('yly_access_token'. $shop_id);
                Cache::rm('yly_refresh_token'. $shop_id);
            }
            Db::rollback();
            return '易联云：' . $e->getMessage();
        }
    }


    public static function testPrint($post, $shop_id)
    {
        try {
            $printer = Printer::where([
                'id' => $post['id'],
                'del' => 0,
                'shop_id' => $shop_id,
                'status' => 1
            ])->findOrEmpty();

            if ($printer->isEmpty()) {
                throw new \Exception('请配置打印机');
            }

            $config = PrinterConfig::getConfigById($printer['config_id'], $shop_id);

            // 易联云
            $yly_print = new YlyPrinter($config['client_id'], $config['client_secret'], $shop_id);

            //获取打印机状态
            $response = $yly_print->getPrintStatus($printer['machine_code']);

            if (1 == $response->body->state) {
                $data = static::testData();
                $template = self::getPrinterTpl($shop_id);
                $yly_print->ylyPrint([['machine_code' => $printer['machine_code'], 'print_number' => $printer['print_number']]], $data, $template);
                return true;
            }
            $msg = '打印机离线';

            if (2 == $response->body->state) {
                $msg = '打印机缺纸';
            }
            throw new \Exception($msg);

        } catch (\Exception $e) {
            $msg = json_decode($e->getMessage(), true);
            if ($msg && isset($msg['error'])) {
                return '易联云：' . $msg['error_description'];
            }
            if (18 === $e->getCode()) {
                Cache::rm('yly_access_token' . $shop_id);
                Cache::rm('yly_refresh_token' . $shop_id);
            }
            return '易联云：' . $e->getMessage();
        }
    }


    /**
     * @notes 打印机配置
     * @param $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/1/20 10:21
     */
    public static function getTypeList($shop_id)
    {
        ConfigLogic::createDefaultConfig($shop_id);
        return PrinterConfig::where(['del' => 0, 'shop_id' => $shop_id])->select()->toArray();
    }

    /**
     * @notes 打印机详情
     * @param $id
     * @param $shop_id
     * @return array|\think\Model
     * @author 段誉
     * @date 2022/1/20 10:21
     */
    public static function getPrinter($id, $shop_id)
    {
        $where = ['del' => 0, 'id' => $id, 'shop_id' => $shop_id];
        return Printer::where($where)->findOrEmpty();
    }


    /**
     * @notes 获取打印机配置
     * @param $shop_id
     * @return array|int|mixed|string|null
     * @author 段誉
     * @date 2022/1/19 19:01
     */
    public static function getPrinterTpl($shop_id)
    {
        return ConfigServer::get('printer', 'yly_template', [], $shop_id);
    }

    /**
     * @notes 设置打印机配置
     * @param $data
     * @param $shop_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/1/19 19:01
     */
    public static function setPrinterTpl($data, $shop_id)
    {
        ConfigServer::set('printer', 'yly_template', $data, $shop_id);
    }


    /**
     * @notes 测试数据
     * @return array
     * @author 段誉
     * @date 2022/1/20 11:03
     */
    public static function testData()
    {
        $order = [
            'order_sn' => date("Ymd") . '88888888888',
            'create_time' => date('Y-m-d H:i:s'),
            'delivery_type' => 1,
            'consignee' => '张先生',
            'mobile' => '138888888888',
            'delivery_address' => '广东省广州市天河区XXXX科技园',
            'user_remark' => '这是用户备注',
            'order_goods' => [
                [
                    'goods_name' => 'iPhone 13',
                    'spec_value_str' => '全网通256G，银色',
                    'spec_value' => '全网通256G，银色',
                    'goods_num' => '88',
                    'goods_price' => '3689',
                    'total_price' => '88888',
                ],
                [
                    'goods_name' => '小米手机Plus',
                    'spec_value_str' => '全网通256G，黑色',
                    'spec_value' => '全网通256G，黑色',
                    'goods_num' => '88',
                    'goods_price' => '3689',
                    'total_price' => '88888',
                ],
                [
                    'goods_name' => '华为 P40',
                    'spec_value_str' => '全网通256G，黑色',
                    'spec_value' => '全网通256G，黑色',
                    'goods_num' => '88',
                    'goods_price' => '3689',
                    'total_price' => '88888',
                ],
            ],
            'selffetch_shop' => [],
            'goods_price' => '888888',  //商品总价
            'discount_amount' => '80',      //优惠金额
            'member_amount' => '80',      //会员金额
            'shipping_price' => '12',      //应付
            'order_amount' => '222'      //应付金额
        ];
        return $order;
    }

}