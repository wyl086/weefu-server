<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
use app\common\command\WechatMiniExpressSendSync;

return [
    // 指令定义
    'commands' => [
        'crontab'            => 'app\common\command\Crontab',
        'order_close'        => 'app\common\command\OrderClose',
        'order_finish'       => 'app\common\command\OrderFinish',
        'distribution_order' => 'app\common\command\DistributionOrder',
        'user_distribution'  => 'app\common\command\UserDistribution', //更新会员分销信息
        'bargain_close'      => 'app\common\command\BargainClose', //更新砍价记录状态
        'team_end'           => 'app\common\command\TeamEnd', //拼团超时关闭
        'password'           => 'app\common\command\Password', //管理员密码
        'award_integral'     => 'app\common\command\AwardIntegral', //结算消费赠送积分
        'wechat_merchant_transfer'     => 'app\common\command\WechatMerchantTransfer', //商家转账到零钱查询
        'wechat_live_room'     => 'app\common\command\WechatLiveRoom', //更新直播间状态
        'wechat_live_goods'     => 'app\common\command\WechatLiveGoods', //更新直播商品状态
    
        // 微信小程序 发货信息同步
        'wechat_mini_express_send_sync' => WechatMiniExpressSendSync::class,
    ],
];
