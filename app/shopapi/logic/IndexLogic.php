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

namespace app\shopapi\logic;

use app\common\basics\Logic;
use app\common\model\shop\Shop;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;

/**
 * 商家移动端管理员默认配置
 * Class LoginLogic
 * @package app\shopapi\logic
 */
class IndexLogic extends Logic
{

    /**
     * @notes 配置信息
     * @return array
     * @author 段誉
     * @date 2021/11/13 17:13
     */
   public static function config()
   {
       $config = [
           'platform_name' => ConfigServer::get('website', 'name'),
       ];
       return $config;
   }

    /**
     * @notes 版权资质
     * @param $shop_id
     * @return mixed
     * @author ljj
     * @date 2022/2/22 11:10 上午
     */
   public static function copyright($shop_id)
   {
       $result = Shop::where('id',$shop_id)->json(['other_qualifications'],true)->field('business_license,other_qualifications')->findOrEmpty()->toArray();
       $business_license = $result['business_license'] ? UrlServer::getFileUrl($result['business_license']) : '';
       foreach ($result['other_qualifications'] as &$val) {
           $other_qualifications[] = UrlServer::getFileUrl($val);
       }
       if (!empty($business_license)) {
           array_unshift($other_qualifications,$business_license);
       }

       return $other_qualifications;
   }

}
