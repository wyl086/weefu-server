<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller;


use app\common\basics\AdminBase;
use app\common\server\FileServer;
use app\common\server\JsonServer;
use Exception;

class Upload extends AdminBase
{
//    public $like_not_need_login = ['image'];

    /**
     * NOTE: 上传图片
     * @author: 张无忌
     */
    public function image()
    {
        try {
            $cid = $this->request->post('cid');
            $result = FileServer::image($cid, 0); // 0 平台

            return JsonServer::success("上传成功", $result);
        } catch (Exception $e) {
            return JsonServer::error($e->getMessage());
        }
    }
}