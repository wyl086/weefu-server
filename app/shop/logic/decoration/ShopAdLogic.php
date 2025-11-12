<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\shop\logic\decoration;

use app\common\basics\Logic;
use app\common\enum\ShopAdEnum;
use app\common\model\distribution\DistributionGoods;
use app\common\model\shop\ShopAd;
use app\common\server\FileServer;
use think\facade\Validate;

class ShopAdLogic extends Logic
{
    static function lists($params, $shop_id)
    {
        $where  = [
            [ 'shop_id', '=', $shop_id ],
        ];
        $page   = $params['page'] ?? 1;
        $limit  = $params['limit'] ?? 10;
        
        $append = [ 'place_name', 'terminal_name','status_name' ];
        
        $lists = ShopAd::where($where)->page($page, $limit)->append($append)->order('id desc')->select()->toArray();
        $count = ShopAd::where($where)->count();
    
        return [ 'count' => $count, 'lists' => $lists ];
    }
    
    static function add($params, $shop_id)
    {
        
        if (empty($params['terminal']) || ! isset(ShopAdEnum::getTerminal()[$params['terminal']])) {
            static::$error = '终端必须选择';
            return false;
        }
        
        if (empty($params['image'])) {
            static::$error = '广告图片必须';
            return false;
        }
        
        $params['shop_id'] = $shop_id;
        ShopAd::create($params, [
            'shop_id',
            'title',
            'place',
            'terminal',
            'image',
            'sort',
            'link',
            'status',
        ]);
        return true;
    }
    
    static function edit($params, $shop_id)
    {
        
        if (empty($params['terminal']) || ! isset(ShopAdEnum::getTerminal()[$params['terminal']])) {
            static::$error = '终端必须选择';
            return false;
        }
        
        if (empty($params['image'])) {
            static::$error = '广告图片必须';
            return false;
        }
        
        $where = [
            [ 'id', '=', $params['id'] ],
            [ 'shop_id', '=', $shop_id ],
        ];
        
        ShopAd::update($params, $where, [
            'shop_id',
            'title',
            'place',
            'terminal',
            'image',
            'sort',
            'link',
            'status',
        ]);
        
        return true;
    }
    
    static function status($params, $shop_id)
    {
        $where = [
            [ 'id', '=', $params['id'] ],
            [ 'shop_id', '=', $shop_id ],
        ];
    
        ShopAd::update($params, $where, [ 'status' ]);
        
        return true;
    }
    
    static function delete($params, $shop_id)
    {
        ShopAd::destroy(function ($query) use ($params, $shop_id) {
            $query->where('shop_id', $shop_id)->where('id', $params['id']);
        });
        
        return true;
    }
    
}