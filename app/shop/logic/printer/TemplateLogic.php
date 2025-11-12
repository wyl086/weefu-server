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

namespace app\shop\logic\printer;

use app\common\basics\Logic;
use app\common\model\printer\Printer;
use app\common\model\printer\PrinterConfig;
use app\common\server\UrlServer;
use app\common\server\YlyPrinter;

/**
 * 小票模板逻辑层
 * Class TemplateLogic
 * @package app\admin\logic\printer
 */
class TemplateLogic extends Logic
{

    /**
     * @notes 模板详情
     * @param $shop_id
     * @return array|\think\Model
     * @author 段誉
     * @date 2022/1/19 16:02
     */
    public static function getDetail($shop_id)
    {
        $result = PrinterLogic::getPrinterTpl($shop_id);
        $result['file_url'] = UrlServer::getFileUrl();
        return $result;
    }

    /**
     * @notes 编辑模板
     * @param $post
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2022/1/19 16:44
     */
    public static function edit($post, $shop_id)
    {
        try {

            PrinterLogic::setPrinterTpl($post, $shop_id);

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * @notes 打印机列表
     * @param $shop_id
     * @return mixed
     * @author 段誉
     * @date 2022/1/19 18:54
     */
    public static function getPrinterList($shop_id)
    {
        $where[] = ['p.del', '=', 0];
        $where[] = ['pc.type', '=', 1]; //类型为易联云
        $where[] = ['p.shop_id', '=', $shop_id];

        $result = (new Printer())->alias('p')
            ->join('printer_config pc', 'p.config_id = pc.id')
            ->where($where)
            ->column('machine_code,private_key,print_number', 'machine_code');
        return $result;
    }
}