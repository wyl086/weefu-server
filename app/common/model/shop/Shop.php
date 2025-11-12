<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model\shop;


use app\common\basics\Models;
use app\common\enum\ShopEnum;
use app\common\server\UrlServer;

/**
 * 商家模型
 * Class Shop
 * @package app\common\model\shop
 */
class Shop extends Models
{
    /**
     * NOTE: 关联主营类目模型
     * @author 张无忌
     */
    public function category()
    {
        return $this->hasOne('ShopCategory', 'id', 'cid');
    }

    /**
     * @Notes: 关联商家账号模型
     * @Author: 张无忌
     */
    public function admin()
    {
        return $this->hasOne('ShopAdmin', 'shop_id', 'id');
    }

    /**
     * NOTE: 获取器-到期时间
     * @author: 张无忌
     * @param $value
     * @return false|string
     */
    public function getExpireTimeAttr($value)
    {
        return $value === 0 ? '无期限' : date('Y-m-d H:i:s', $value);
    }
    
    /**
     * @notes is_expire 店铺是否已过期
     * @param $fieldValue
     * @param $data
     * @return int
     * @author lbzy
     * @datetime 2023-09-01 15:54:55
     */
    function getIsExpireAttr($fieldValue, $data)
    {
        $time = $this->getOrigin('expire_time');
        return $time === 0 ? 0 : (time() > $time ? 1 : 0);
    }

    /**
     * 修改器-logo转相对
     * @param $value
     * @return mixed|string
     */
    public function setLogoAttr($value)
    {
        return $value ? UrlServer::setFileUrl($value) : '';
    }

    /**
     * 获取器-背景图路径
     * @param $value
     * @return string
     */
    public function getBackgroundAttr($value)
    {
        return $value ? UrlServer::getFileUrl($value) : '';
    }

    /**
     * 获取器-背景图路径
     * @param $value
     * @return string
     */
    public function getLogoAttr($value)
    {
        return $value ? UrlServer::getFileUrl($value) : '';
    }

    /**
     * 获取器-pc店铺封面路径
     * @param $value
     * @return string
     */
    public function getCoverAttr($value)
    {
        return $value ? UrlServer::getFileUrl($value) : '';
    }

    /**
     * 获取器-pc店铺头图路径
     * @param $value
     * @return string
     */
    public function getBannerAttr($value)
    {
        return $value ? UrlServer::getFileUrl($value) : '';
    }

    /**
     * @Notes: 修改器-工作日
     * @Author: 张无忌
     * @param $value
     * @return string
     */
    public function setWeekdaysAttr($value)
    {
        if ($value) {
            return implode(',', $value);
        }

        return '';
    }

    /**
     * @Notes: 获取器-工作日
     * @Author: 张无忌
     * @param $value
     * @return array
     */
    public function getWeekdaysAttr($value)
    {
        if ($value) {
            return explode(',', $value);
        }

        return [];
    }

    /**
     * @Notes: 获取器-退货地址
     * @Author: 张无忌
     * @param $value
     * @return array|mixed
     */
    public function getRefundAddressAttr($value)
    {
        if ($value) {
            return json_decode($value, true);
        }

        return [];
    }

    /**
     * 商家类型 获取器
     */
    public function getTypeDescAttr($value, $data)
    {
        return ShopEnum::getShopTypeDesc($data['type']);
    }

    /**
     * 商家介绍 获取器
     */
    public function getIntroAttr($value)
    {
        return $value ? $value : '';
    }

    /**
     * @notes 到期状态
     * @param $value
     * @return false|string
     * @author 段誉
     * @date 2022/3/16 18:10
     */
    public function getExpireDescAttr($value, $data)
    {
        if ($data['expire_time'] && time() > $data['expire_time']) {
            return '已到期';
        }
        return '未到期';
    }


    /**
     * @notes 获取器-配送方式
     * @param $value
     * @param $data
     * @return false|string[]
     * @author 段誉
     * @date 2022/11/1 11:34
     */
    public function getDeliveryTypeAttr($value, $data)
    {
        if (!empty($value)) {
            return explode(',', $value);
        }
        return $value;
    }


    /**
     * @notes 修改器-配送方式
     * @param $value
     * @param $data
     * @return string
     * @author 段誉
     * @date 2022/11/1 11:35
     */
    public function setDeliveryTypeAttr($value, $data)
    {
        if (!empty($value)) {
            return implode(',', $value);
        }
        return $value;
    }

}