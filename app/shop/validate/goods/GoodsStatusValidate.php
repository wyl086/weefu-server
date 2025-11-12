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
namespace app\shop\validate\goods;

use app\common\basics\Validate;
use app\common\model\bargain\Bargain;
use app\common\model\goods\Goods;
use app\common\model\seckill\SeckillGoods;
use app\common\model\team\TeamActivity;


/**
 * 商品状态验证
 * Class GoodsStatusValidate
 * @package app\shop\validate\goods
 */
class GoodsStatusValidate extends Validate
{
    protected $rule = [
        'ids' => 'require|checkIds',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'ids.require' => '请选择商品',
        'status.require' => '参数缺失',
        'status.in' => '状态错误',
    ];


    //活动商品不可编辑
    protected function checkIds($value, $rule, $data)
    {
        if (!is_array($value)) {
            return '参数错误';
        }

        foreach ($value as $item) {
            $result = $this->checkGoods($item, $data['shop_id']);
            if (true !== $result) {
                return $result;
            }
        }

        return true;
    }


    // 验证商品
    protected function checkGoods($goods_id, $shop_id)
    {
        $goods = Goods::where(['del' => 0, 'id' => $goods_id, 'shop_id' => $shop_id])->findOrEmpty();
        if ($goods->isEmpty()) {
            return '包含不存在商品';
        }

        $condition = [
            'del' => 0,
            'goods_id' => $goods_id,
            'shop_id' => $shop_id
        ];

        // 砍价
        $bargain = Bargain::where($condition)->findOrEmpty();
        if (!$bargain->isEmpty()) {
            return '所选商品中包含砍价活动商品, 无法修改';
        }

        // 秒杀
        $seckillGoods = SeckillGoods::where($condition)->findOrEmpty();
        if (!$seckillGoods->isEmpty()) {
            return '所选商品中包含秒杀活动商品, 无法修改';
        }

        // 拼团
        $teamGoods = TeamActivity::where($condition)->findOrEmpty();
        if (!$teamGoods->isEmpty()) {
            return '所选商品中包含拼团活动商品, 无法修改';
        }

        return true;
    }


}