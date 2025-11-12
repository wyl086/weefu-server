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

use app\common\enum\ShopAdEnum;

class ShopAd extends \app\common\basics\Models
{
    function getPlaceNameAttr($fieldValue, $data)
    {
        return ShopAdEnum::getPlaceDesc($data['place']);
    }
    
    function getTerminalNameAttr($fieldValue, $data)
    {
        return ShopAdEnum::getTerminal($data['terminal']);
    }
    
    function getStatusNameAttr($fieldValue, $data)
    {
        return $data['status'] == 1 ? '正常' : '关闭';
    }
    
    function getLinkPathAttr($fieldValue, $data)
    {
        return parse_url($data['link'], PHP_URL_PATH);
    }
    
    function getLinkQueryAttr($fieldValue, $data)
    {
        parse_str(parse_url($data['link'], PHP_URL_QUERY), $arr);
        return $arr;
    }
}