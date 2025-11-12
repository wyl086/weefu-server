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
namespace app\shop\controller\coupon;

use app\common\basics\ShopBase;
use app\common\server\JsonServer;
use app\shop\logic\coupon\CouponLogic;
use app\shop\validate\coupon\CouponValidate;
use think\exception\ValidateException;

class Coupon extends ShopBase{
    /**
     * note 优惠券列表
     */
    public function lists(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $get['shop_id'] = $this->shop_id;
            $data = CouponLogic::lists($get);
            return JsonServer::success('', $data);
        }

        return view();
    }
    /**
     * note 添加优惠券
     */
    public function add(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            try{
                validate(CouponValidate::class)->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $post['shop_id'] = $this->shop_id;
            $result = CouponLogic::add($post);
            if($result === true) {
                return JsonServer::success('新增成功');
            }
            return JsonServer::error(CouponLogic::getError());
        }

        return view();
    }
    /**
     * note 编辑优惠券
     */
    public function edit(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            try{
                validate(CouponValidate::class)->check($post);
            }catch(ValidateException $e) {
                return JsonServer::error($e->getError());
            }
            $post['shop_id'] = $this->shop_id;
            $result = CouponLogic::edit($post);
            if($result === true) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error(CouponLogic::getError());

        }
        $id = $this->request->get('id', '', 'intval');
        $detail = CouponLogic::getCoupon($id,true);
        return view('', [
            'detail' => json_encode($detail, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * note 优惠券详情
     */
    public function detail(){
        $id = $this->request->get('id');
        $detail = CouponLogic::getCoupon($id,true);
        return view('', [
            'detail' => json_encode($detail,JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * note 优惠券发放记录
     */
    public function log(){
        if($this->request->isAjax()){
            $get = $this->request->get();
            $data = CouponLogic::log($get);
            return JsonServer::success('', $data);
        }

        $id = $this->request->get('id');
        return view('', [
            'id' => $id
        ]);
    }

    /**
     * 上下架
     */
    public function changeStatus()
    {
        $id = $this->request->post('id');
        $result = CouponLogic::changeStatus($id);
        if($result === true) {
            return JsonServer::success('操作成功');
        }
        return JsonServer::error(CouponLogic::getError());
    }


    /**
     * note 删除优惠券
     */
    public function del(){
        if($this->request->isAjax()){
            $id = $this->request->post('id');
            $result = CouponLogic::del($id);
            if($result === true){
                return JsonServer::success('删除成功');
            }
            return JsonServer::error(CouponLogic::getError());
        }
    }


    /**
     * 发放优惠券
     */
    public function sendCouponList(){
        return view();
    }

    public function getShopCouponList()
    {
        $get = $this->request->get();
        $get['shop_id'] = $this->shop_id;
        $data = CouponLogic::getShopCouponList($get);
        return JsonServer::success('', $data);
    }

    public function sendCoupon(){
        if($this->request->isAjax()){
            $post = $this->request->post();
            $result = CouponLogic::sendCoupon($post);
            if($result === true){
                return JsonServer::success('发放成功');
            }
            return JsonServer::error(CouponLogic::getError());
        }
    }
}