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

namespace app\shop\controller\printer;

use app\common\basics\ShopBase;
use app\shop\logic\printer\PrinterLogic;
use app\common\server\JsonServer;
use app\shop\validate\printer\PrinterValidate;

/**
 * 打印机管理控制器
 * Class Printer
 * @package app\admin\controller\printer
 */
class Printer extends ShopBase
{

    /**
     * @notes 打印机列表
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/1/19 10:34
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $result = PrinterLogic::lists($get, $this->shop_id);
            return JsonServer::success('', $result);
        }
        return view();
    }

    /**
     * Notes:添加打印机
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $post['shop_id'] = $this->shop_id;
            (new PrinterValidate())->goCheck('add', $post);
            $result = PrinterLogic::add($post, $this->shop_id);
            if (true === $result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error($result);
        }
        return view('', [
            'type_list' => PrinterLogic::getTypeList($this->shop_id)
        ]);
    }


    /**
     * @notes 编辑打印机
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/1/20 10:59
     */
    public function edit()
    {
        $id = $this->request->get('id/d');
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['del'] = 0;
            $post['shop_id'] = $this->shop_id;
            (new PrinterValidate())->goCheck('', $post);
            $result = PrinterLogic::edit($post, $this->shop_id);
            if (true === $result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error($result);
        }
        return view('', [
            'type_list' => PrinterLogic::getTypeList($this->shop_id),
            'detail' => PrinterLogic::getPrinter($id, $this->shop_id)
        ]);
    }


    /**
     * @notes 删除打印机
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/1/20 11:00
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            (new PrinterValidate())->goCheck('del');
            $result = PrinterLogic::del($id, $this->shop_id);
            if (true === $result) {
                return JsonServer::success('操作成功');
            }
            return JsonServer::error($result);
        }
        return JsonServer::error('操作失败');
    }

    
    public function testPrint()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['shop_id'] = $this->shop_id;
            (new PrinterValidate())->goCheck('config', $post);
            $result = PrinterLogic::testPrint($post, $this->shop_id);
            if (true === $result) {
                return JsonServer::success('打印成功');
            }
            return JsonServer::error($result);
        }
        return JsonServer::error('操作失败');
    }

}