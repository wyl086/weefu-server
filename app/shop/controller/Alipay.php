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
namespace app\shop\controller;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\AlipayLogic;
use app\shop\validate\AlipayValidate;

class Alipay extends ShopBase
{
    function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = AlipayLogic::lists($get, $this->shop_id);
            return JsonServer::success('获取成功', $lists);
        }
        
        return view();
    }
    
    function detail()
    {
        return view('', [
            'detail'    => AlipayLogic::detail($this->request->get('id')),
        ]);
    }
    
    function add()
    {
        if ($this->request->isAjax()) {
            (new AlipayValidate())->goCheck('add');
            $post = $this->request->post();
            $res = AlipayLogic::add($post, $this->shop_id);
            if ($res === false) {
                $error = AlipayLogic::getError() ?: '新增失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('新增成功');
        }
        
        return view();
    }
    
    function edit()
    {
        if ($this->request->isAjax()) {
            (new AlipayValidate())->goCheck('edit');
            $post = $this->request->post();
            $res = AlipayLogic::edit($post, $this->shop_id);
            if ($res === false) {
                $error = AlipayLogic::getError() ?: '编辑失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('编辑成功');
        }
        
        $id = $this->request->get('id');
        return view('', [
            'detail' => AlipayLogic::detail($id)
        ]);
    }
    
    function del()
    {
        if ($this->request->isAjax()) {
            (new AlipayValidate())->goCheck('id');
            $id = $this->request->post('id');
            $res = AlipayLogic::del($id, $this->shop_id);
            if ($res === false) {
                $error = AlipayLogic::getError() ?: '删除失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('删除成功');
        }
        
        return JsonServer::error('异常');
    }
}