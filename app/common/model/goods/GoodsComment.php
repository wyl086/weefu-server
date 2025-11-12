<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\goods;


use app\common\basics\Models;
use app\common\model\order\OrderGoods;

class GoodsComment extends Models
{
    /**
     * 关联商品
     */
    public function goods()
    {
        return $this->hasOne(Goods::class, 'id', 'goods_id')
            ->field('id,name,image');
    }

    /**
     * 关联SKU
     */
    public function goodsItem()
    {
        return $this->hasOne(GoodsItem::class, 'id', 'item_id')
            ->field('id,image,spec_value_str');
    }

    /**
     * 关联订单商品
     */
    public function orderGoods()
    {
        return $this->hasOne(OrderGoods::class, 'id', 'order_goods_id')
            ->field('id,total_pay_price');
    }

    /**
     * 关联图片评论
     */
    public function goodsCommentImage()
    {
        return $this->hasMany(GoodsCommentImage::class, 'goods_comment_id', 'id');
    }

    public function getStatusDescAttr($value)
    {
        return $value ? '显示' : '隐藏';
    }

    public function getGoodsCommentDescAttr($value)
    {
       $desc = [1=>'差评',2=>'差评',3=>'中评',4=>'好评',5=>'好评',];
       return $desc[$value];
    }
    
    /**
     * @notes 评论内容 xss
     * @param $comment
     * @return mixed|string
     * @author lbzy
     * @datetime 2023-09-06 14:36:25
     */
    function getCommentAttr($comment)
    {
        if (in_array(app('http')->getName(), [ 'admin', 'shop' ]) && request()->isAjax()) {
            $comment = htmlspecialchars($comment);
        }
        
        return $comment;
    }
}