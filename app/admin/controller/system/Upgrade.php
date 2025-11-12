<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller\system;

use app\admin\logic\system\UpgradeLogic;
use app\admin\validate\UpgradeValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 升级更新
 * Class Upgrade
 * @package app\admin\controller
 */
class Upgrade extends AdminBase
{

    /**
     * Notes: 更新列表页
     * @author 段誉(2021/7/12 16:17)
     * @return \think\response\Json|\think\response\View
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $page = $this->request->get('page/d', $this->page_no);
            $size = $this->request->get('limit/d', $this->page_size);
            $data = UpgradeLogic::index($page, $size);
            return JsonServer::success('', $data);
        }
        return view();
    }


    /**
     * Notes: 提示
     * @author 段誉(2021/7/12 16:17)
     * @return \think\response\View
     */
    public function choosePage()
    {
        return view();
    }


    /**
     * Notes: 执行更新
     * @author 段誉(2021/7/12 16:18)
     * @return \think\response\Json
     */
    public function handleUpgrade()
    {
        if ($this->request->isAjax()) {
            (new UpgradeValidate())->goCheck();
            $post = $this->request->post();
            $post['update_type'] = 1;
            $post['link'] = "package_link";
            $res = UpgradeLogic::upgrade($post);
            if (true === $res) {
                return JsonServer::success('更新成功');
            } else {
                return JsonServer::error(UpgradeLogic::getError() ?? '系统错误');
            }
        }
        return JsonServer::error('更新失败');
    }


    /**
     * Notes: 添加日志
     * @author 段誉(2021/7/12 16:18)
     * @return \think\response\Json
     */
    public function getPkg()
    {
        $post = $this->request->post();
        $res = UpgradeLogic::getPkgLine($post);
        if($res === false) {
            return JsonServer::error(UpgradeLogic::getError() ?? '系统错误');
        }
        return JsonServer::success('', $res);
    }


}