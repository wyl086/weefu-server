<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\shop\validate\live;

use app\common\basics\Validate;
use app\common\model\live\LiveGoods;

/**
 * 直播商品验证器
 * Class LiveGoodsValidate
 * @package app\shop\validate\live
 */
class LiveGoodsValidate extends Validate
{

    protected $rule = [
        'id' => 'require|checkLiveGoods',
        'name' => 'require|length:3,14',
        'price_type' => 'require',
        'url' => 'require',
        'cover_img' => 'require',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'name.require' => '请输入商品名称',
        'name.length' => '商品名称长度在3~14个汉字',
        'cover_img' => '请选择商品封面',
    ];

    protected function sceneAdd()
    {
        return $this->remove(['id' => 'require']);
    }



    protected function sceneDel()
    {
        return $this->only(['id']);
    }


    protected function sceneDetail()
    {
        return $this->only(['id']);
    }

    

    /**
     * @notes 校验直播商品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2023/2/16 11:10
     */
    protected function checkLiveGoods($value, $rule, $data)
    {
        $room = LiveGoods::where([
            'id' => $value,
            'shop_id' => $data['shop_id'],
            'del' => 0
        ])->findOrEmpty();

        if ($room->isEmpty()) {
            return '直播商品不存在';
        }
        return true;
    }

}