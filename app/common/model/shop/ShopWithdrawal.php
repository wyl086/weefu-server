<?php


namespace app\common\model\shop;


use app\common\basics\Models;
use app\common\server\UrlServer;

class ShopWithdrawal extends Models
{
    /**
     * @Notes: 关联商家模型
     * @Author: 张无忌
     */
    public function shop()
    {
        return $this->hasOne('Shop', 'id', 'shop_id');
    }
    
    /**
     * @notes 关联提现支付宝账号
     * @return \think\model\relation\HasOne
     * @author lbzy
     * @datetime 2023-06-07 14:24:07
     */
    function alipay()
    {
        return $this->hasOne(ShopAlipay::class, 'id', 'alipay_id');
    }

    /**
     * @Notes: 获取器-转换图片路径
     * @Author: 张无忌
     * @param $value
     * @return string
     */
    public function getTransferVoucherAttr($value)
    {
        if ($value) {
            return UrlServer::getFileUrl($value);
        }

        return '';
    }

    /**
     * @Notes: 修改器-转换图片路径
     * @Author: 张无忌
     * @param $value
     * @return string
     */
    public function setTransferVoucherAttr($value)
    {
        if ($value) {
            return UrlServer::setFileUrl($value);
        }

        return '';
    }
}