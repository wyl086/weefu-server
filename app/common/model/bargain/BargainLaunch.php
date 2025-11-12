<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\bargain;


use app\common\basics\Models;
use app\common\model\user\User;
use app\common\model\order\Order;
use app\common\server\UrlServer;

/**
 * 砍价活动 参与模型
 * Class BargainLaunch
 * @Author 张无忌
 * @package app\common\model
 */
class BargainLaunch extends Models
{
    protected $json = ['goods_snap', 'bargain_snap'];
    protected $jsonAssoc = true;

    const conductStatus = 0;  //进行中
    const successStatus = 1;  //成功
    const failStatus = 2; //失败

    /**
     * @notes 砍价状态描述
     * @param bool $type
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:41 下午
     */
    public static function getStatusDesc($type = true)
    {

        $desc = [
            self::conductStatus => '砍价中',
            self::successStatus => '砍价成功',
            self::failStatus => '砍价失败',
        ];
        if ($type === true) {
            return $desc;
        }
        return $desc[$type] ?? '未知';
    }

    /**
     * @notes 关联用户模型
     * @return \think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:41 下午
     */
    public function user()
    {

        return $this->hasOne(user::class, 'id', 'user_id')
            ->field('id,sn,nickname,avatar,level,mobile,sex,create_time');
    }

    /**
     * @notes 关联订单模型
     * @return BargainLaunch|\think\model\relation\HasOne
     * @author suny
     * @date 2021/7/13 6:41 下午
     */
    public function order()
    {

        return $this->hasOne(Order::class, 'id', 'order_id')
            ->field('id,order_sn,user_id,create_time,order_amount,order_status');
    }

    /**
     * @notes 关联砍价助力bargain_knife
     * @return \think\model\relation\HasMany
     * @author suny
     * @date 2021/7/13 6:41 下午
     */
    public function bargainKnife()
    {

        return $this->hasMany('bargain_knife', 'launch_id', 'id');
    }

    /**
     * @notes 显示商品价格
     * @param $value
     * @param $data
     * @return mixed
     * @author suny
     * @date 2021/7/13 6:41 下午
     */
    public function getPriceAttr($value, $data)
    {

        return $data['goods_snap']['price'];
    }

//    //显示用户头像
//    public function getAvatarAttr($value,$data){
//        return UrlServer::getFileUrl($data['user']['avatar']);
//    }


    /**
     * @notes 显示商品主图
     * @param $value
     * @param $data
     * @return string
     * @author suny
     * @date 2021/7/13 6:41 下午
     */
    public function getGoodsImageAttr($value, $data)
    {

        return UrlServer::getFileUrl($data['goods_snap']['goods_iamge']);
    }

    /**
     * @notes 显示商品名称
     * @param $value
     * @param $data
     * @return mixed
     * @author suny
     * @date 2021/7/13 6:42 下午
     */
    public function getNameAttr($value, $data)
    {

        return $data['goods_snap']['name'];
    }

    /**
     * @notes 显示商品规格ID
     * @param $value
     * @param $data
     * @return mixed
     * @author suny
     * @date 2021/7/13 6:42 下午
     */
    public function getItemIdAttr($value, $data)
    {

        return $data['goods_snap']['item_id'];
    }

    /**
     * @notes 显示规格名称
     * @param $value
     * @param $data
     * @return mixed
     * @author suny
     * @date 2021/7/13 6:42 下午
     */
    public function getSpecValueStrAttr($value, $data)
    {

        return $data['goods_snap']['spec_value_str'];
    }

    /**
     * @notes 砍价按钮
     * @param $value
     * @param $data
     * @return string
     * @author suny
     * @date 2021/7/13 6:42 下午
     */
    public function getBtnTipsAttr($value, $data)
    {

        $tips = '';
        if (1 == $data['status']) {
            $tips = '砍价成功';
        } else if (0 == $data['status']) {
            $tips = '继续砍价';
        }
        return $tips;
    }
//    //显示直接购买按钮
//    public function getBuyBtnAttr($value,$data){
//        $btn = 0;
//        if(2 == $data['bargain_snap']['payment_where'] && empty($data['order_id'])){
//            $btn = 1;
//        }
//        return $btn;
//    }
    /**
     * @notes 显示查看订单按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:42 下午
     */
    public function getOrderBtnAttr($value, $data)
    {

        $btn = 0;
        if ($data['order_id']) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 继续砍价按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:42 下午
     */
    public function getBargainBtnAttr($value, $data)
    {

        $btn = 0;
        if (0 == $data['status']) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 砍价成功按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:42 下午
     */
    public function getSuccessBtnAttr($value, $data)
    {

        $btn = 0;
        if (1 == $data['status']) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 显示去支付按钮
     * @param $value
     * @param $data
     * @return int
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getPayBtnAttr($value, $data)
    {

        $btn = 0;
        if (1 == $data['status']) {
            $btn = 1;
        }
        return $btn;
    }

    /**
     * @notes 显示砍价提示
     * @param $value
     * @param $data
     * @return string
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getBargainTipsAttr($value, $data)
    {

        $tips = '须砍至最低价才可支付购买';
        if (2 == $data['bargain_snap']['payment_where']) {
            $tips = '砍至任意金额可直接购买';
        }
        return $tips;
    }

    /**
     * @notes 显示状态
     * @param $value
     * @param $data
     * @return string|string[]
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getStatusTextAttr($value, $data)
    {

        return static::getStatusDesc($data['status']);
    }

    /**
     * @notes 发起砍价时间
     * @param $value
     * @param $data
     * @return false|string
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getCreateTimeAttr($value, $data)
    {

        return date('Y-m-d H:i:s', $data['launch_start_time']);
    }

    /**
     * @notes 砍价结束时间
     * @param $value
     * @param $data
     * @return mixed
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getOverTimeAttr($value, $data)
    {

        return $data['launch_end_time'];
    }

    /**
     * @notes 剩余的差价
     * @param $value
     * @param $data
     * @return float
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getDiffPriceAttr($value, $data)
    {

        return round($data['current_price'] - $data['bargain_price'], 2);
    }

    /**
     * @notes 已砍价的价格
     * @param $value
     * @param $data
     * @return float
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getKnifePriceAttr($value, $data)
    {

        return round($data['goods_snap']['price'] - $data['current_price'], 2);
    }

    /**
     * @notes 砍价进度条
     * @param $value
     * @param $data
     * @return float
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getProgressAttr($value, $data)
    {

        if ($data['current_price'] == 0) {
            return round(1, 2);
        } else {
            return round($data['bargain_price'] / $data['current_price'], 2);
        }
    }

    /**
     * @notes 活动价
     * @param $value
     * @param $data
     * @return mixed
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getActivityPriceAttr($value, $data)
    {

        return $data['bargain_snap']['floor_price'];
    }

    /**
     * @notes 显示砍价状态提示
     * @param $value
     * @param $data
     * @return string
     * @author suny
     * @date 2021/7/13 6:43 下午
     */
    public function getStatusTipsAttr($value, $data)
    {

        if (2 == $data['status']) {
            return '非常遗憾，砍价失败了';
        }
        if (1 == $data['status']) {
            return '恭喜您，砍价成功';
        }
        return '';
    }

    /**
     * @notes 显示用户信息
     * @param $value
     * @param $data
     * @return array
     * @author suny
     * @date 2021/7/13 6:44 下午
     */
    public function getKnifeListAttr($value, $data)
    {

        $list = [];
        foreach ($this->bargain_knife as $knife) {

            $list[] = [
                'id' => $knife['id'],
                'user_id' => $knife['user']['id'],
                'nickname' => $knife['user']['nickname'],
                'avatar' => UrlServer::getFileUrl($knife['user']['avatar']),
                'help_price' => $knife['help_price'],
                'help_time' => date('Y-m-d H:i:s', $knife['help_time']),
            ];
        }
        return $list;
    }

    /**
     * @notes 分享标题
     * @param $value
     * @param $data
     * @return mixed
     * @author suny
     * @date 2021/7/13 6:44 下午
     */
    public function getShareTitlesAttr($value, $data)
    {

        return $data['bargain_snap']['share_title'];
    }

    /**
     * @notes 分享简介
     * @param $value
     * @param $data
     * @return mixed
     * @author suny
     * @date 2021/7/13 6:44 下午
     */
    public function getShareIntrosAttr($value, $data)
    {

        return $data['bargain_snap']['share_intro'];
    }
}