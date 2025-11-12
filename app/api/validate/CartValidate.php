<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\api\validate;

use app\common\basics\Validate;
use app\common\model\Cart;


class CartValidate extends Validate
{
    protected $rule = [
        'cart_id' => 'require|checkCart',
        'goods_num' => 'require|integer|gt:0',
        'item_id' => 'require',
        'selected' => 'require|in:0,1',
    ];

    protected $message = [
        'item_id' => '请选择商品',
        'goods_num.require' => '商品数量不能为0',
        'goods_num.gt' => '商品数量需大于0',
        'goods_num.integer' => '商品数量需为整数',
        'cart_id.require' => '参数错误',
        'selected.require' => '参数错误',
        'selected.in' => '参数错误',
    ];

    protected $scene = [
        'add'       => ['item_id', 'goods_num'],
        'del'       => ['cart_id'],
        'selected'  => ['cart_id', 'selected'],
        'change'    => ['cart_id', 'goods_num'],
    ];


    protected function checkCart($value, $rule, $data)
    {
        $cart = Cart::where(['id' => $value, 'user_id' => $data['user_id']])->find();
        if (!$cart) {
            return '购物车不存在';
        }
        return true;
    }
}