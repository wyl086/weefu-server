<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\RechargeTemplate;
use app\common\model\RechargeOrder;
use app\common\server\ConfigServer;
use app\common\enum\PayEnum;
use think\facade\Db;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;

class RechargeLogic extends Logic
{
    public static function getTemplate(){
        $list = RechargeTemplate::where(['del'=>0])
            ->order('sort desc')
            ->field('id,money,give_money,is_recommend')
            ->select()
            ->toArray();

        foreach ($list as &$item){
            $item['tips'] = '';
            if($item['give_money'] > 0){
                $item['tips'] = '充'.intval($item['money']).'赠送'.intval($item['give_money']).'元';
            }
        }
        return $list;
    }

    public static function recharge($user_id,$client,$post)
    {
        try{
            $give_growth  = ConfigServer::get('recharge', 'give_growth', 0);

            //充值模板
            if(isset($post['id'])){
                $template = RechargeTemplate::where(['del'=>0,'id'=>$post['id']])
                    ->field('id,money,give_money')
                    ->findOrEmpty();
                if($template->isEmpty()) {
                    throw new \think\Exception('充值模板不存在');
                }
                $money = $template['money'];
                $give_money = $template['give_money'];

            }else{//自定义充值金额
                $template = RechargeTemplate::where(['del'=>0,'money'=>$post['money']])
                    ->field('id,money,give_money')
                    ->findOrEmpty();
                $money = $post['money'];
                $give_money = 0;
                if(!$template->isEmpty()){
                    $money = $template['money'];
                    $give_money = $template['give_money'];
                }
            }
            //赠送的积分和成长值
            $growth = $money * $give_growth;
            $growth = $growth > 0 ? intval($growth) : 0;

            $add_order = [
                'user_id'       => $user_id,
                'order_sn'      => createSn('recharge_order','order_sn'),
                'order_amount'  => $money,
                'order_source'  => $client,
                'pay_status'    => PayEnum::UNPAID,    //待支付状态；
                'pay_way'       => $post['pay_way'] ?? 1,
                'template_id'   => $template['id'] ?? 0,
                'give_money'    => $give_money,
                'give_growth'   => $growth,
                'create_time'   => time(),
            ];

            $id = Db::name('recharge_order')->insertGetId($add_order);
            if($id){
                return Db::name('recharge_order')->where(['id'=>$id])->field('id,order_sn,give_growth')->find();
            }
            return [];
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function rechargeRecord($get)
    {
        $list = RechargeOrder::field('order_sn, order_amount, give_money, create_time')
            ->where([
                'user_id' => $get['user_id'],
                'pay_status' => PayEnum::ISPAID, // 已支付的
            ])
            ->order('create_time', 'desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();
        $count = RechargeOrder::where([
                'user_id' => $get['user_id'],
                'pay_status' => PayEnum::ISPAID
            ])
            ->count();

        foreach($list as &$item) {
            if($item['give_money'] > 0) {
                $item['desc'] = '充值'. clearZero($item['order_amount']) . '赠送' . clearZero($item['give_money']);
            }else{
                $item['desc'] = '充值'. clearZero($item['order_amount']);
            }
            $item['total'] = $item['order_amount'] + $item['give_money']; // 充值金额 + 赠送金额
        }

        $result = [
            'count' => $count,
            'lists' => $list,
            'more' =>  is_more($count, $get['page_no'], $get['page_size']),
            'page_no' =>  $get['page_no'],
            'page_size' =>  $get['page_size']
        ];

        return $result;
    }
}
