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
use app\common\enum\FreightEnum;
use app\common\model\Freight;

/**
 * 单个规格验证
 * Class GoodsOneSpec
 * @package app\shop\validate\goods
 */
class GoodsOneSpec extends Validate
{
    protected $rule = [
        'one_market_price'      => [],
        'one_price'             => 'require|egt:0.01|checkExpress',
        'one_stock'             => 'require|integer|egt:0',
        'one_weight'            => [],
        'one_volume'            => [],
        'one_chengben_price'    => [],
    ];

    protected $message = [
        'one_market_price.require'  => '请输入价格',
        'one_market_price.egt'      => '价格必须大于或等于0.01',
        'one_price.require'         => '请输入价格',
        'one_price.egt'             => '价格必须大于或等于0.01',
        'one_chengben_price.require'=> '请输入成本价',
        'one_chengben_price.egt'    => '成本价必须大于或等于0.01',
        'one_stock.require'         => '请输入库存',
        'one_stock.integer'         => '库存必须为整型',
        'one_stock.egt'              => '库存必须大于或等于0',
        'one_weight.require'        => '请输入重量',
        'one_weight.egt'            => '重量必须为大于或等于0',
        'one_volume.require'        => '请输入体积',
        'one_volume.egt'            => '体积必须为大于或等于0',
    ];
    
    
    
    function checkExpress($value, $rule, $data)
    {
        $express_type = input('express_type');
        
        // 运费模版
        if ($express_type != 3) {
            return true;
        }
        
        $freight = Freight::where('id', input('express_template_id/d'))->findOrEmpty();
        
        if (empty($freight['id'])) {
            return '运费模板不存在';
        }
        
        switch ($freight['charge_way']) {
            case FreightEnum::CHARGE_WAY_WEIGHT:
                if (empty($data['one_weight']) || $data['one_weight'] <= 0) {
                    return '当前运费模板是按重量计算运费，请输入重量';
                }
                break;
            case FreightEnum::CHARGE_WAY_VOLUME:
                if (empty($data['one_volume']) || $data['one_volume'] <= 0) {
                    return '当前运费模板是按体积计算运费，请输入体积';
                }
                break;
            default:
                break;
        }
        
        return true;
    }

}