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
use app\common\{
    enum\GoodsEnum,
    model\Cart,
    basics\Logic,
    model\goods\Goods,
    enum\FootprintEnum
};

/**
 * 购物车逻辑层
 * Class CartLogic
 * @package app\api\logic
 */
class CartLogic extends Logic
{


    /**
     * @notes 购物车列表
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/9/7 10:23
     */
    public static function lists($user_id)
    {
        $carts = Cart::with(['goods', 'goods_item', 'shop'])
            ->where('user_id', $user_id)
            ->order('id desc')
            ->select()->toArray();

        $goods_num = 0;
        $total = 0;
        $lists = [];

        $shop_ids = array_unique(array_column($carts, 'shop_id'));

        foreach ($shop_ids as $shop_id) {

            $lists[$shop_id]['is_selected'] = 1;
            $shop_info = $cart_lists = [];

            foreach ($carts as $k => $cart) {
                if ($shop_id != $cart['shop_id']) {
                    continue;
                }
                if (empty($cart['goods']['id']) || $cart['goods']['del'] != 0) {
                    continue;
                }
                if (empty($shop_info)) {
                    $shop_info = [
                        'shop_id'   => $cart['shop']['id'],
                        'shop_name' => $cart['shop']['name'],
                        'type'      => $cart['shop']['type'],
                        'is_pay'    => $cart['shop']['is_pay'],
                    ];
                }

                $sub_price = 0; // 已选中 && 上架 && 未删除 && 规格信息不为空 && 商家支付功能开启
                if ($cart['selected'] == 1 && $cart['goods']['status'] == 1 && $cart['goods']['del'] == 0
                    && !empty($cart['goods_item']) && $cart['shop']['is_pay'])
                {
                    $goods_num += $cart['goods_num'];
                    $total += $cart['goods_item']['price'] * $cart['goods_num'];
                    $sub_price = round($cart['goods_item']['price'] * $cart['goods_num'], 2);
                } else {
                    $cart['selected'] = 0;
                }

                // 设置商家选中状态; 满足条件(未选中的商品,上架,未删除,有规格信息,商家支付功能开启) 才影响商家选中状态
                if(!$cart['selected']) {
                    if ($cart['goods']['status'] == 1 && $cart['goods']['del'] == 0
                        && !empty($cart['goods_item']) && $cart['shop']['is_pay']) {
                        $lists[$shop_id]['is_selected'] = 0;
                    }
                }

                $cart_lists[] = [
                    'cart_id'           => $cart['id'],
                    'goods_id'          => $cart['goods_id'],
                    'goods_name'        => $cart['goods']['name'],
                    'image'             => empty($cart['goods_item']['image']) ? $cart['goods']['image'] : $cart['goods_item']['image'],
                    'goods_num'         => $cart['goods_num'],
                    'goods_status'      => $cart['goods']['status'],
                    'goods_del'         => $cart['goods']['del'],
                    'spec_value_str'    => $cart['goods_item']['spec_value_str'] ?? '请重新选择规格',
                    'price'             => $cart['goods_item']['price'],
                    'stock'             => $cart['goods_item']['stock'],
                    'selected'          => intval($cart['selected']),
                    'item_id'           => $cart['item_id'],
                    'sub_price'         => $sub_price,
                    'is_pay'            => $cart['shop']['is_pay'],
                    'has_item'          => empty($cart['goods_item']) ? 0 : 1, //是否有规格信息
                ];
            }
            
            if (empty($shop_info)) {
                unset($lists[$shop_id]);
                continue;
            }
            
            $lists[$shop_id]['shop'] = $shop_info;
            $lists[$shop_id]['cart'] = $cart_lists;
        }

        return [
            'lists' => array_values($lists),
            'total_amount' => round($total, 2),
            'total_num' => $goods_num,
        ];
    }



    /**
     * Notes: 添加
     * @param $post
     * @param $user_id
     * @return bool
     * @author 段誉(2021/5/10 19:03)
     */
    public static function add($post, $user_id)
    {
        try {
            $item_id = $post['item_id'];
            $goods_num = $post['goods_num'];

            $cart = Cart::where(['user_id' => $user_id, 'item_id' => $item_id])->find();
            $cart_num = $post['goods_num'] + (isset($cart) ? $cart['goods_num'] : 0);

            $goods = self::checkCartGoods($item_id, $cart_num);
            if (false === $goods) {
                throw new \Exception(self::getError() ?: '商品信息错误');
            }

            if ($cart) {
                //购物车内已有该商品
                Cart::where(['id' => $cart['id'], 'shop_id' => $goods['shop_id']])
                    ->update(['goods_num' => $goods_num + $cart['goods_num']]);
            } else {
                //新增购物车记录
                Cart::create([
                    'user_id' => $user_id,
                    'goods_id' => $goods['id'],
                    'goods_num' => $goods_num,
                    'item_id' => $item_id,
                    'shop_id'  => $goods['shop_id'],
                ]);
            }

            // 记录访问足迹
            event('Footprint', [
                'type'    => FootprintEnum::ADD_CART,
                'user_id' => $user_id,
                'foreign_id' => $goods['id']
            ]);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * Notes: 变动数量
     * @param $cart_id
     * @param $goods_num
     * @author 段誉(2021/5/11 11:59)
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function change($cart_id, $goods_num)
    {
        $cart = Cart::find($cart_id);
        $goods_num = ($goods_num <= 0) ? 1 : $goods_num;
        if (false === self::checkCartGoods($cart['item_id'], $goods_num)) {
            return false;
        }
        Cart::update(['goods_num' => $goods_num], ['id' => $cart_id]);
        return true;
    }


    /**
     * Notes: 删除
     * @param $cart_id
     * @param $user_id
     * @author 段誉(2021/5/11 12:02)
     * @return bool
     */
    public static function del($cart_id, $user_id)
    {
        return Cart::where(['id' => $cart_id, 'user_id' => $user_id])->delete();
    }


    /**
     * Notes: 更改选中状态
     * @param $post
     * @param $user_id
     * @author 段誉(2021/5/11 15:49)
     * @return Cart
     */
    public static function selected($post, $user_id)
    {
        return Cart::where(['user_id' => $user_id, 'id' => $post['cart_id']])
            ->update(['selected' => $post['selected']]);
    }


    /**
     * Notes: 购物车数量
     * @param $user_id
     * @author 段誉(2021/5/11 12:07)
     * @return array
     */
    public static function cartNum($user_id)
    {
        $cart = new Cart();
        $num = $cart->alias('c')
            ->join('goods g', 'g.id = c.goods_id')
            ->join('goods_item i', 'i.id = c.item_id')
            ->where(['g.status' => 1, 'g.del' => 0, 'c.user_id' => $user_id])
            ->sum('goods_num');
        return ['num' => $num ?? 0];
    }


    /**
     * Notes: 验证商品
     * @param $item_id
     * @param $goods_num
     * @author 段誉(2021/5/11 11:59)
     * @return bool
     */
    public static function checkCartGoods($item_id, $goods_num)
    {
        $goodsModel = new Goods();
        $goods = $goodsModel->alias('g')
            ->with('shop')
            ->field('g.id, g.status, g.del, g.shop_id, g.type,i.stock')
            ->join('goods_item i', 'i.goods_id = g.id')
            ->where('i.id', $item_id)
            ->find();

        if (!$goods['shop']['is_pay']) {
            self::$error = '该商家支付功能已关闭';
            return false;
        }

        if (empty($goods) || $goods['status'] == 0 || $goods['del'] != 0) {
            self::$error = '商品不存在或已下架';
            return false;
        }

        if ($goods['stock'] < $goods_num) {
            self::$error = '很抱歉,库存不足';
            return false;
        }

        if ($goods['type'] == GoodsEnum::TYPE_VIRTUAL) {
            self::$error = '虚拟商品不可加入购物车';
            return false;
        }

        return $goods;
    }


}