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

use app\common\logic\ExpressLogic;
use app\common\logic\FreightLogic;
use app\common\model\Freight as FreightModel;
use app\admin\validate\FreightValidate;
use app\common\basics\ShopBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;
use think\exception\ValidateException;


/**
 * 运费模板设置
 * Class Freight
 * @package app\shop\controller
 */
class Freight extends ShopBase
{

    /**
     * User: 意象信息科技 mjf
     * Desc: 设置快递方式
     */
    public function set()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $post['type'] = isset($post['type']) && $post['type'] == 'on' ? 1 : 0;
            ConfigServer::set('express', 'is_express', $post['type']);
            return JsonServer::success('操作成功');
        }
        $type = ConfigServer::get('express', 'is_express');
        return view('', [
            'type' => $type
        ]);
    }

    /**
     * User: 意象信息科技 mjf
     * Desc: 运费模板列表
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            return JsonServer::success('获取成功', FreightLogic::lists($get));//运费模板页
        }
        return view('index', [
            'charge_way_lists' => FreightModel::getChargeWay(true),
            'config'=>ExpressLogic::getExpress()
        ]);
    }

    /**
     * User: 意象信息科技 mjf
     * Desc: 添加运费模板
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            try {
                $post = $this->request->post();
                validate(FreightValidate::class)->scene('add')->check($post);
                $post['shop_id'] = $this->shop_id;
                FreightLogic::add($post);
                return JsonServer::success('添加成功');
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
        }
        return view('',[]);
    }

    /**
     * User: 意象信息科技 mjf
     * Desc: 删除运费模板
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            try {
                $post = $this->request->post();
                validate(FreightValidate::class)->scene('del')->check($post);
                FreightLogic::del($post);
                return JsonServer::success('删除成功');
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }
        }
        return view('',[]);
    }

    /**
     * User: 意象信息科技 mjf
     * Desc: 运费模板详情
     */
    public function detail()
    {
        $id = $this->request->get('id');
        $detail = FreightLogic::detail($id);
        return view('',[
            'detail'=>$detail
        ]);
    }


    /**
     * User: 意象信息科技 mjf
     * Desc: 运费模板编辑
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            try {
                $post = $this->request->post();
                validate(FreightValidate::class)->scene('edit')->check($post);
                FreightLogic::edit($post);
                return JsonServer::success('编辑成功');
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }

        }
        $id = $this->request->get('id');

        $detail = FreightLogic::detail($id);

        return view('',[
            'detail'=>$detail
        ]);
    }



    public function area()
    {
        return view();
    }

    //编辑页的地区选择
    public function areaEdit()
    {
        return view();
    }


}