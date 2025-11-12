<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\shop;


use app\admin\logic\shop\TreatyLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 入驻协议
 * Class Treaty
 * @package app\admin\controller\shop
 */
class Treaty extends AdminBase
{
    public function index()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = TreatyLogic::set($post);
            if ($res === false) {
                $error = TreatyLogic::getError() ?: '更新失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('更新成功');
        }

        return view('', [
            'detail' => TreatyLogic::detail()
        ]);
    }
}