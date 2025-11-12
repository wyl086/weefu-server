<?php

namespace app\api\controller;


use app\api\logic\OrderInvoiceLogic;
use app\api\validate\OrderInvoiceValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;


/**
 * 发票
 * Class OrderInvoice
 * @package app\api\controller
 */
class OrderInvoice extends Api
{

    /**
     * @notes 提交发票
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/12 10:15
     */
    public function add()
    {
        $post = $this->request->post();
        $params = (new OrderInvoiceValidate())->goCheck('add', $post);
        $result = OrderInvoiceLogic::add($params);
        if (false === $result) {
            $error = OrderInvoiceLogic::getError() ?: '提交失败';
            return JsonServer::error($error);
        }
        return JsonServer::success('提交成功', [], 1, 1);
    }



    /**
     * @notes 编辑发票
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/12 10:35
     */
    public function edit()
    {
        $post = $this->request->post();
        $params = (new OrderInvoiceValidate())->goCheck('edit', $post);
        $result = OrderInvoiceLogic::edit($params);
        if (false === $result) {
            $error = OrderInvoiceLogic::getError() ?: '编辑失败';
            return JsonServer::error($error);
        }
        return JsonServer::success('操作成功', [], 1, 1);
    }

    

    /**
     * @notes 发票详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/12 12:16
     */
    public function detail()
    {
        $params = (new OrderInvoiceValidate())->goCheck('detail');
        $result = OrderInvoiceLogic::detail($params);
        return JsonServer::success('获取成功', $result);
    }


    /**
     * @notes 获取商家发票设置
     * @return \think\response\Json
     * @author 段誉
     * @date 2022/4/12 15:33
     */
    public function setting()
    {
        $params = (new OrderInvoiceValidate())->goCheck('setting');
        $result = OrderInvoiceLogic::getInvoiceSetting($params);
        return JsonServer::success('获取成功', $result);
    }

}
