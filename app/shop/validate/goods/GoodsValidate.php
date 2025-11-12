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
use app\common\enum\GoodsEnum;
use app\common\enum\ShopEnum;
use app\common\model\goods\GoodsBrand;
use app\common\model\goods\GoodsUnit;
use app\common\model\goods\Supplier;
use app\common\model\goods\Goods;
use app\common\model\bargain\Bargain;
use app\common\model\seckill\SeckillGoods;
use app\common\model\shop\Shop;
use app\common\model\team\TeamActivity;
use think\Db;


/**
 * 商品-验证
 * Class GoodsValidate
 * @package app\admin\validate
 */
class GoodsValidate extends Validate
{
    protected $rule = [
        // 商品类型 0-实物商品 1-虚拟商品
        'type'                      => 'require|in:0,1',
        'goods_id'                  => 'require|checkActivityGoods',
        'name'                      => 'require|min:3|max:64|checkName',
        'first_cate_id'             => 'require',
        'image'                     => 'require',
        'goods_image'               => 'require|length:1,5',
        'spec_type'                 => 'require',

        // 配送方式 1-快递配送 2-虚拟发货
        'delivery_type'             => 'require|checkDeliveryType',
        // 买家付款后  1-自动大货 2-手动发货
        'after_pay'                 => 'requireIf:type,1',
        // 发货后 1-自动完成订单 2-需要买家确认收货
        'after_delivery'            => 'requireIf:type,1',
        'delivery_content'          => 'requireIf:type,1',

        // 快递类型 1-包邮；2-统一运费；3-运费模板
        'express_type'              => 'requireIf:type,0',
        'express_money'             => 'requireIf:express_type,2|checkExpressMoney',
        'express_template_id'       => 'requireIf:express_type,3',
        'status'                    => 'require',
        'stock_warn'                => 'integer|egt:0',
        'code'                      => 'checkCode',
        'sort'                      => 'egt:0',
    ];

    protected $message = [
        'type.require'                          => '请选择商品类型',
        'type.in'                               => '商品类型参数错误',
        'name.require'                          => '请输入商品名称',
        'name.min'                              => '商品名称长度至少3个字符',
        'name.max'                              => '商品名称长度最多64个字符',
        'name.unique'                           => '商品名称已存在，请重新输入',
        'first_cate_id.require'                 => '至少选择一个分类',
        'image.require'                         => '请上传商品主图',
        'goods_image.require'                   => '请上传商品轮播图',
        'goods_image.length'                    => '商品轮播图最多只能上传5张',
        'spec_type.require'                     => '请选择规格类型',

        'delivery_type.require'                 => '请选择配送方式',
        'after_pay.requireIf'                   => '请选择买家付款后发货方式',
        'after_delivery.requireIf'              => '请选择发货后是否自动完成订单',
        'delivery_content.requireIf'            => '请填写发货内容',

        'express_type.require'                  => '请选择快递运费类型',
        'express_money.requireIf'               => '请输入统一运费',
        'express_template_id.requireIf'         => '请选择快递运费模板',
        'status.require'                        => '请选择销售状态',
        'stock_warn.integer'                    => '库存预警须为整数',
        'stock_warn.egt'                        => '库存预警须大于或等于0',
        'sort.egt'                              => '排序值不能小于0',
    ];

    public function sceneAdd()
    {
        $this->remove('goods_id', 'require');
    }

    public function sceneDel()
    {
        $this->only(['goods_id']);
    }

    /**
     * 校验商品名称
     */
    public function checkName($value, $rule, $data)
    {
        $where = [
            ['del', '=', 0],
            ['name', '=', $value],
            ['shop_id', '=', $data['shop_id']]
        ];
        if($data['goods_id']) { // 编辑
            $where[] = ['id', '<>', $data['goods_id']];
        }
        $goods = Goods::where($where)->findOrEmpty();
        if(!$goods->isEmpty()) {
            return '商品名称已存在,请更换其他名称';
        }
        return true;
    }


    //活动商品不可编辑
    public function checkActivityGoods($value, $rule, $data)
    {
        $condition = [
            'goods_id' => $value,
            'del' => 0,
            'shop_id' => $data['shop_id']
        ];

        // 砍价
        $bargain = Bargain::where($condition)->findOrEmpty();
        if(!$bargain->isEmpty()) {
            return '商品正在参与砍价活动, 无法修改';
        }

        // 秒杀
        $seckillGoods = SeckillGoods::where($condition)->findOrEmpty();
        if (!$seckillGoods->isEmpty()) {
            return '商品正在参与秒杀活动, 无法修改';
        }

        // 拼团
        $teamGoods = TeamActivity::where($condition)->findOrEmpty();
        if(!$teamGoods->isEmpty()){
            return '商品正在参加拼团活动, 无法修改';
        }

        return true;
    }

    /**
     * @notes 校验商品编码唯一性
     */
    public function checkCode($value, $rule, $data)
    {
        $where = [
            ['code', '=', $data['code']],
            ['del', '=', 0],
        ];
        if($data['goods_id']) {
            $where[] = ['id', '<>', $data['goods_id']];
        }
        $goods = Goods::where($where)->select()->toArray();
        if($goods) {
            return '商品编码已存在';
        }
        return true;
    }

    public function checkExpressMoney($value, $rule, $data)
    {
        if ($data['express_type'] == 2 && $value < 0) {
            return '统一运费不能小于0';
        }
        return true;
    }

    /**
     * @notes 校验配送方式
     * @param $value
     * @param $rule
     * @param $data
     * @return string|void
     * @author 段誉
     * @date 2022/4/7 12:04
     */
    public function checkDeliveryType($value, $rule, $data)
    {
        // 虚拟商品
        if ($data['type'] == GoodsEnum::TYPE_VIRTUAL && !in_array(GoodsEnum::DELIVERY_VIRTUAL, $value)) {
            return '虚拟商品配送方式需选择虚拟发货';
        }

        // 实物商品
        if ($data['type'] == GoodsEnum::TYPE_ACTUAL) {
            if (!in_array(GoodsEnum::DELIVERY_EXPRESS, $value) && !in_array(GoodsEnum::DELIVERY_SELF, $value)) {
                return '实物商品配送方式需选择快递发货或线下自提';
            }

            $shop = Shop::findOrEmpty($data['shop_id']);

            // 选择快递配送 但商家不支持快递配送
            if (in_array(GoodsEnum::DELIVERY_EXPRESS, $value) && !in_array(ShopEnum::DELIVERY_EXPRESS, $shop['delivery_type'])) {
                return '请先到商家设置开启快递配送方式';
            }

            // 选择自提 但商家不支持自提
            if (in_array(GoodsEnum::DELIVERY_SELF, $value) && !in_array(ShopEnum::DELIVERY_SELF, $shop['delivery_type'])) {
                return '请先到商家设置开启线下自提方式';
            }
        }

        return true;
    }
}