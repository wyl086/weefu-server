<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\enum\AfterSaleEnum;
use app\common\enum\OrderEnum;
use app\common\enum\WithdrawEnum;
use app\common\model\AfterSale;
use app\common\model\distribution\Distribution;
use app\common\model\FootprintRecord;
use app\common\model\goods\GoodsComment;
use app\common\model\kefu\ChatRelation;
use app\common\model\order\Order;
use app\common\model\Session;
use app\common\model\user\User;
use app\common\model\user\UserAuth;
use app\common\model\WithdrawApply;
use think\facade\Db;
use think\facade\Log;

class UserDeleteLogic extends Logic
{
    /**
     * @notes 检测是否可注销
     * @param $user_id
     * @return int[]
     * @author lbzy
     * @datetime 2023-08-21 16:37:38
     */
    static function checkCanDelete($user_id) : array
    {
        $order_status = [
            OrderEnum::ORDER_STATUS_NO_PAID,
            OrderEnum::ORDER_STATUS_DELIVERY,
            OrderEnum::ORDER_STATUS_GOODS,
        ];
        
        $after_status = [
            AfterSaleEnum::STATUS_ING,
            AfterSaleEnum::STATUS_GOODS_RETURNED,
            AfterSaleEnum::STATUS_RECEIVE_GOODS,
        ];
        
        $withdraw_status = [
            WithdrawEnum::STATUS_ING,
            WithdrawEnum::STATUS_WAIT,
        ];
        
        $status     = User::where('id', $user_id)->where('del', 0)->value('disable') == 0 ? 1 : 0;
        $order      = Order::where('user_id', $user_id)->where('del', 0)->where('order_status', 'IN', $order_status)->value('id') ? 0 : 1;
        $after_sale = AfterSale::where('user_id', $user_id)->where('del', 0)->where('status', 'IN', $after_status)->value('id') ? 0 : 1;
        $withdraw   = WithdrawApply::where('user_id', $user_id)->where('status', 'in', $withdraw_status)->value('id') ? 0 : 1;
        
        $result = [
            'data'  => [
                // 是否冻结
                'status'       => [
                    'pass'          => $status,
                    'msg'           => $status ? '通过' : '账号冻结中，无法申请注销',
                ],
                // 是否有未完成订单
                'order'        => [
                    'pass'          => $order,
                    'msg'           => $order ? '通过' : '存在未完成订单，无法申请注销',
                ],
                // 是否有售后处理中
                'after_sale'   => [
                    'pass'          => $after_sale,
                    'msg'           => $after_sale ? '通过' : '存在售后订单，无法申请注销',
                ],
                // 提现申请
                'withdraw'     => [
                    'pass'          => $withdraw,
                    'msg'           => $withdraw ? '通过' : '存在佣金待提现申请，无法申请注销',
                ],
            ],
            'pass'  => 1,
            'msg'   => '通过',
        ];
        
        foreach ($result['data'] as $info) {
            if ($info['pass'] == 0) {
                $result['pass'] = 0;
                $result['msg']  = $info['msg'];
                break;
            }
        }
        
        return $result;
    }
    
    /**
     * @notes 确定注销
     * @param $user_id
     * @return bool|string
     * @author lbzy
     * @datetime 2023-08-21 16:47:13
     */
    static function sureDelete($user_id)
    {
        $check = static::checkCanDelete($user_id);
        
        if ($check['pass'] == 0) {
            return $check['msg'];
        }
        
        try {
            Db::startTrans();
            
            // 用户数据
            User::update([
                'user_delete'       => 1,
                'disable'           => 1,
                
                'account'           => '',
                'password'          => '',
                'pay_password'      => '',
                'mobile'            => '',
                
                'first_leader'              => 0,
                'second_leader'             => 0,
                'third_leader'              => 0,
                'ancestor_relation'         => '',
                'is_distribution'           => 0,
                'freeze_distribution'       => 1,
                // 'distribution_code'         => '',
            ], [ [ 'id', '=', $user_id ] ]);
            
            // 用户openid unionid
            UserAuth::destroy(function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
            
            // 用户token
            Session::destroy(function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
            
            // 用户客服
            ChatRelation::destroy(function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
            
            // 用户分销关系清除
            User::update([ 'first_leader' => 0, 'second_leader' => 0, 'third_leader' => 0 ], [ [ 'first_leader', '=', $user_id ] ]);
            User::update([ 'second_leader' => 0, 'third_leader' => 0 ], [ [ 'second_leader', '=', $user_id ] ]);
            User::update([ 'third_leader' => 0 ], [ [ 'third_leader', '=', $user_id ] ]);
            // 分销冻结
            Distribution::update([
                'is_distribution'       => 0,
                'is_freeze'             => 1,
            ], [ [ 'user_id', '=', $user_id ] ]);
            
            // 足迹气泡
            FootprintRecord::destroy(function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
            
            // 商品评论
            GoodsComment::destroy(function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
            
            Db::commit();
            return true;
        } catch(\Throwable $e) {
            static::$error = $e->getMessage();
            Db::rollback();
            Log::write($e->__toString(), 'user_delete_error');
            return $e->getMessage();
        }
    }
}