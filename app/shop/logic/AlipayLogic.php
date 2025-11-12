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
namespace app\shop\logic;

use app\common\basics\Logic;
use app\common\model\shop\ShopAlipay;

class AlipayLogic extends Logic
{
    static function lists($get, $shop_id)
    {
        try {
            $model = new ShopAlipay();
            $lists = $model->field(true)
                ->where(['del' => 0, 'shop_id'=>$shop_id])
                ->order('id', 'desc')
                ->paginate([
                    'page'      => $get['page'] ?? 1,
                    'list_rows' => $get['limit'] ?? 20,
                    'var_page'  => 'page'
                ])->toArray();
            
            return [ 'count'=>$lists['total'], 'lists'=>$lists['data'] ];
        } catch (\Exception $e) {
            return [ 'error'=>$e->getMessage() ];
        }
    }
    
    static function detail($id)
    {
        $model = new ShopAlipay();
        return $model->field(true)->findOrEmpty($id);
    }
    
    static function add($post, $shop_id)
    {
        try {
            ShopAlipay::create([
                'shop_id'   => $shop_id,
                'account'   => $post['account'],
                'username'  => $post['username'],
                'del'       => 0,
            ]);
            
            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
    
    static function edit($post, $shop_id)
    {
        try {
            ShopAlipay::update([
                'account'   => $post['account'],
                'username'  => $post['username'],
                'del'       => 0,
            ], [ 'id' => $post['id'], 'shop_id' => $shop_id ]);
            
            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
    
    static function del($id, $shop_id)
    {
        try {
            ShopAlipay::update([
                'del'         => 1,
                'update_time' => time()
            ], [ 'id' => $id, 'shop_id' => $shop_id ]);
            
            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
    
    static function getAlipayByShopId($shop_id)
    {
        try {
            $model = new ShopAlipay();
            return $model->field(true)
                ->where(['del' => 0, 'shop_id'=>$shop_id])
                ->order('id', 'desc')
                ->select()->toArray();
        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }
}