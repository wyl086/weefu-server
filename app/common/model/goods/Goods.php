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
use app\common\model\distribution\DistributionGoods;
use app\common\model\shop\Shop;
use app\common\server\UrlServer;


/**
 * 商品-模型
 * Class Goods
 * @package app\common\model\goods
 */
class Goods extends Models
{
    /**
     * 商品轮播图 关联模型
     */
    public function GoodsImage()
    {
        return $this->hasMany('GoodsImage', 'goods_id', 'id')->field('goods_id, uri');
    }
    /**
     * 商品SKU 关联模型
     */
    public function GoodsItem()
    {
        return $this->hasMany('GoodsItem', 'goods_id', 'id')
            ->field('id, goods_id, image, spec_value_ids, spec_value_str, market_price, price, stock, chengben_price');
    }

    /**
     * 店铺 关联模型
     */
    public function Shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id')
            ->field('id, name, logo, type, star, score, intro,is_pay, mobile,is_freeze,is_run,expire_time')
            ->append([ 'is_expire' ]);
    }

    public function getIsDistributionDescAttr($value, $data)
    {
        return $data['is_distribution'] ? '是': '否';
    }
    
    function getIsMemberDescAttr($value, $data)
    {
        return $data['is_member'] ? '是': '否';
    }

    /**
     * 根据商品id获取商品名称
     */
    public function getGoodsNameById($goods_id)
    {
        return $this->where('id',$goods_id)->value('name');

    }

    /**
     * 根据商品id查询商品是否上架
     */
    public function checkStatusById($goods_id)
    {
        $status = $this
            ->where([
                ['id','=',$goods_id],
                ['del','=',0],
            ])
            ->value('status');
        if ($status){
            if ($status == 1){
                return true;
            }
            if (empty($status) || $status ===0){
                return  false;
            }
        }
        return false;
    }


    /**
     * 根据goods_id查询商品配送方式及所需信息
     */
    public function getExpressType($goods_id)
    {
        return $this->where('id',$goods_id)->column('express_type,express_money,express_template_id')[0];
    }

    /**
     * 最小值与最大值范围
     */
    public function getMinMaxPriceAttr($value, $data)
    {
        return '¥ ' . $data['min_price'] . '~ ¥ '. $data['max_price'];
    }

    /**
     * @notes 商品是否参与分销
     * @param $value
     * @return string
     * @author Tab
     * @date 2021/9/1 17:29
     */
    public function getDistributionFlagAttr($value)
    {
        $data = DistributionGoods::where('goods_id', $value)->findOrEmpty()->toArray();
        if (!empty($data) && $data['is_distribution'] == 1) {
            return true;
        }
        return false;
    }


    /**
     * @notes 商品详情
     * @param $value
     * @param $data
     * @return array|string|string[]|null
     * @author 段誉
     * @date 2022/6/13 10:50
     */
    public function getContentAttr($value,$data){
/*        $preg = '/(<img .*?src=")[^https|^http](.*?)(".*?>)/is';*/
//        $local_url = UrlServer::getFileUrl('/');
//        return  preg_replace($preg, "\${1}$local_url\${2}\${3}",$value);
        $content = $data['content'];
        if (!empty($content)) {
            $content = HtmlGetImage($content);
        }
        return $content;
    }
    public function setContentAttr($value,$data)
    {
        $content = $data['content'];
        if (!empty($content)) {
            $content = HtmlSetImage($content);
        }
        return $content;
    }

    /**
     * @notes 分销状态搜索器
     * @param $query
     * @param $value
     * @param $data
     * @author Tab
     * @date 2021/9/2 9:55
     */
    public function searchIsDistributionAttr($query, $value, $data)
    {
        // 不参与分销
        if (isset($data['is_distribution']) && $data['is_distribution'] == '0') {
            // 先找出参与分销的商品id
            $ids = DistributionGoods::where('is_distribution', 1)->column('goods_id');
            // 在搜索条件中将它们排除掉
            $query->where('id', 'not in', $ids);

        }
        // 参与分销
        if (isset($data['is_distribution']) && $data['is_distribution'] == '1') {
            // 先找出参与分销的商品id
            $ids = DistributionGoods::where('is_distribution', 1)->column('goods_id');
            // 在搜索条件中使用它们来进行过滤
            $query->where('id', 'in', $ids);
        }
    }
}