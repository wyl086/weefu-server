<?php


namespace app\api\controller;


use app\api\logic\ShopApplyLogic;
use app\api\validate\ShopApplyValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;
use app\common\model\Treaty;
use app\common\enum\TreatyEnum;

class ShopApply extends Api
{
    public $like_not_need_login = ['getTreaty'];
    /**
     * @Notes: 商家申请入驻
     * @Author: 张无忌
     */
    public function apply()
    {
        (new ShopApplyValidate())->goCheck('apply');
        $post = $this->request->post();
        $res = ShopApplyLogic::apply($post, $this->user_id);
        if ($res === false) {
            $error = ShopApplyLogic::getError() ?: '申请失败';
            return JsonServer::error($error);
        }
        return JsonServer::success('申请成功', $res);
    }

    /**
     * @Notes: 申请记录列表
     * @Author: 张无忌
     */
    public function record()
    {
        $get = $this->request->get();
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;
        $lists = ShopApplyLogic::record($get, $this->user_id);
        return JsonServer::success('获取成功', $lists);
    }

    /**
     * @Notes: 申请详细
     * @Author: 张无忌
     */
    public function detail()
    {
        $id = $this->request->get('id');
        $detail = ShopApplyLogic::detail($id);
        return JsonServer::success('获取成功', $detail);
    }

    /**
     * 入驻协议
     */
    public function getTreaty()
    {
       $content = Treaty::where(['type'=>TreatyEnum::SHOP_ENTER_TYPE, 'name'=>'入驻协议'])->value('content');
       if(!$content) {
           $content = [];
       }
       return JsonServer::success('', [
           'content' => $content
       ]);
    }
}