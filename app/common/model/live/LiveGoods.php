<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model\live;

use app\common\basics\Models;
use app\common\enum\LiveGoodsEnum;
use app\common\model\goods\Goods;
use app\common\model\shop\Shop;


/**
 * 直播商品
 * Class LiveGoods
 * @package app\common\model\live
 */
class LiveGoods extends Models
{

    /**
     * @notes 关联商家
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2023/2/16 12:10
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id')
            ->field(['id','logo','type','name'])->append(['type_desc']);
    }


    /**
     * @notes 审核状态描述
     * @param $value
     * @param $data
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/16 9:45
     */
    public function getAuditStatusTextAttr($value, $data)
    {
        return LiveGoodsEnum::getAuditStatusDesc($data['sys_audit_status']);
    }


    /**
     * @notes 价格描述
     * @param $value
     * @param $data
     * @return string
     * @author 段誉
     * @date 2023/2/16 20:45
     */
    public function getPriceTextAttr($value, $data)
    {
        if ($data['price_type'] == LiveGoodsEnum::PRICE_ONE) {
            return '¥' . $data['price'];
        } else {
            return '¥' . $data['price'] . '~ ¥' .$data['price2'];
        }
    }


    /**
     * @notes 价格形式
     * @param $value
     * @param $data
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/16 21:55
     */
    public function getPriceTypeTextAttr($value, $data)
    {
        return LiveGoodsEnum::getPriceTypeDesc($data['price_type']);
    }


    /**
     * @notes 价格描述
     * @param $value
     * @param $data
     * @return string|void
     * @author 段誉
     * @date 2023/2/16 21:59
     */
    public function getPriceTipsAttr($value, $data)
    {
        if ($data['price_type'] == LiveGoodsEnum::PRICE_ONE) {
            return '价格 ' . $data['price'] . '元';
        }
        if ($data['price_type'] == LiveGoodsEnum::PRICE_RANGE) {
            return '价格 ' . $data['price'] . '元 ~ ' .$data['price2'] . '元';
        }
        if ($data['price_type'] == LiveGoodsEnum::PRICE_DISCOUNT) {
            return '价格 ' . $data['price'] . '元  现价 ' .$data['price2'] . '元';
        }
    }


    /**
     * @notes 来源类型
     * @param $value
     * @param $data
     * @return string|string[]
     * @author 段誉
     * @date 2023/2/17 9:36
     */
    public function getSourceTypeTextAttr($value, $data)
    {
        return LiveGoodsEnum::getSourceTypeDesc($data['source_type']);
    }


    /**
     * @notes 商品库存
     * @param $value
     * @param $data
     * @return int|mixed
     * @author 段誉
     * @date 2023/2/17 12:12
     */
    public function getGoodsStockAttr($value, $data)
    {
        $stock = 0;
        if ($data['source_type'] == LiveGoodsEnum::SOURCE_TYPE_GOODS) {
            $stock = Goods::where('id', $data['source_id'])->value('stock');
            $stock = empty($stock) ? 0 : $stock;
        }
        return $stock;
    }



}