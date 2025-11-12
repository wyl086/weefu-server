<?php

namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\JsonServer;
use app\api\logic\AfterSaleLogic;
use think\exception\ValidateException;
use app\api\validate\AfterSaleValidate;
use think\facade\Validate;

/**
 * 售后
 * Class AfterSale
 * @package app\api\controller
 */
class AfterSale extends Api
{
    /**
     * @notes 售后退款列表
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:04 下午
     */
    public function lists()
    {

        $type = $this->request->get('type', 'normal');
        $lists = AfterSaleLogic::lists($this->user_id, $type, $this->page_no, $this->page_size);
        return JsonServer::success('', $lists);
    }

    /**
     * @notes 申请售后
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:05 下午
     * @throws \think\Exception
     */
    public function add()
    {

        $post = $this->request->post();
        $post['user_id'] = $this->user_id;
        (new AfterSaleValidate())->goCheck('add', $post);
        $result = AfterSaleLogic::add($post, $this->user_id);
        if (false === $result) {
            return JsonServer::error('该订单已超过售后时间');
        }
        return JsonServer::success('售后申请成功!', $result);
    }


    /**
     * @notes 售后商品信息
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:05 下午
     */
    public function goodsInfo()
    {

        $get = $this->request->get();
        (new AfterSaleValidate())->goCheck('goodsInfo', $get);
        return JsonServer::success('', AfterSaleLogic::info($get['item_id'], $get['order_id']));
    }


    /**
     * @notes 上传退货快递信息
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:05 下午
     */
    public function express()
    {

        $post = $this->request->post();
        (new AfterSaleValidate())->goCheck('express', $post);
        $data = AfterSaleLogic::express($this->user_id, $post);
        return JsonServer::success('提交成功！', [$data]);
    }

    /**
     * @notes 撤销售后
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:06 下午
     */
    public function cancel()
    {

        $post = $this->request->post();
        (new AfterSaleValidate())->goCheck('cancel', $post);
        AfterSaleLogic::cancel($this->user_id, $post);
        return JsonServer::success('撤销成功');
    }


    /**
     * @notes 售后详情
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:06 下午
     */
    public function detail()
    {

        $get = $this->request->get();
        (new AfterSaleValidate())->goCheck('detail', $get);
        return JsonServer::success('', AfterSaleLogic::detail($get));
    }


    /**
     * @notes 重新申请
     * @return \think\response\Json
     * @throws \think\Exception
     * @author suny
     * @date 2021/7/13 6:06 下午
     */
    public function again()
    {

        $post = $this->request->post();
        (new AfterSaleValidate())->goCheck('again', $post);
        $result = AfterSaleLogic::again($this->user_id, $post);
        return JsonServer::success('提交成功！', $result);
    }
}