<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\shop;


use app\common\basics\Logic;
use app\common\enum\TreatyEnum;
use app\common\model\Treaty;

class TreatyLogic extends Logic
{
    /**
     * NOTE: 设置入驻协议
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function set($post)
    {
        try {
            $treaty = Treaty::where(['type'=>TreatyEnum::SHOP_ENTER_TYPE])->findOrEmpty();
            $time = time();
            if($treaty->isEmpty()) { // 新增
                $addData = [
                    'name' => '入驻协议',
                    'content' => $post['content'] ?? '',
                    'type' => TreatyEnum::SHOP_ENTER_TYPE,
                    'create_time' => $time,
                    'update_time' => $time
                ];
                Treaty::create($addData);
            }else{ // 更新
                Treaty::update([
                    'name'    => '入驻协议',
                    'content' => $post['content'] ?? '',
                    'update_time' => $time
                ], ['type'=>TreatyEnum::SHOP_ENTER_TYPE]);
            }
            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 获取协议详细
     * @author: 张无忌
     * @return array
     */
    public static function detail()
    {
        $model = new Treaty();
        return $model->field(true)
            ->where(['type'=>TreatyEnum::SHOP_ENTER_TYPE])
            ->findOrEmpty()->toArray();
    }
}