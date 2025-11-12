<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\controller\setting;


use app\admin\logic\NoticeSettingLogic;
use app\common\basics\AdminBase;
use app\common\enum\NoticeEnum;
use app\common\server\JsonServer;
use think\Db;

/**
 * 通知设置
 * Class NoticeSetting
 * @package app\admin\controller\setting
 */
class NoticeSetting extends AdminBase
{

    /**
     * Notes: 消息设置列表
     * @author 段誉(2021/4/27 17:17)
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $type = $get['type'] ?? NoticeEnum::NOTICE_USER;
            return JsonServer::success('获取成功', NoticeSettingLogic::lists($type));
        }
        return view();
    }



    /**
     * Notes: 设置系统通知模板
     * @author 段誉(2021/4/27 17:18)
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function set()
    {
        $id = $this->request->get('id');
        $type = $this->request->get('type');
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            NoticeSettingLogic::set($post);
            return JsonServer::success('操作成功');
        }
        return view('set_'.$type, [
            'info' => NoticeSettingLogic::info($id, $type),
            'type' => $type
        ]);
    }

    /**
     * 通知记录
     */
    public function record()
    {
        if($this->request->isAjax()) {
            $get = $this->request->get();
            $data = NoticeSettingLogic::record($get);
            return JsonServer::success('', $data);
        }
        $param = $this->request->get();
        return view('', ['param' => $param]);
    }

    /**
     * 删除记录，直接删除（非软删除）
     */
    public function delRecord()
    {
        $id = $this->request->post('id', '', 'intval');
        if(empty($id)) {
            return JsonServer::error('参数缺失,删除失败');
        }
        $res = Db::name('notice')->delete($id);
        if(!$res) {
            return JsonServer::error('删除失败');
        }
        return JsonServer::success('删除成功');
    }
}