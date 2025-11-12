<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\admin\controller\kefu;
use app\admin\logic\kefu\KefuLangLogic;
use app\admin\logic\kefu\KefuLogic;
use app\admin\validate\kefu\KefuLangValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;

/**
 * 客服术语
 * Class KefuLang
 * @package app\admin\controller\kefu
 */
class KefuLang extends AdminBase
{
    /**
     * @notes 列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     * @author cjhao
     * @date 2021/11/29 15:20
     */
    public function lists()
    {
        if($this->request->isAjax()){
            $page = $this->request->get('page', 1);
            $limit = $this->request->get('limit', 10);
            $lists = KefuLangLogic::lists($limit,$page);
            return JsonServer::success('获取成功', $lists);
        }
        return view();
    }


    /**
     * @notes 添加话术
     * @return \think\response\Json|\think\response\View
     * @author cjhao
     * @date 2021/11/29 15:59
     */
    public function add()
    {
        if($this->request->isAjax()){
            $post= (new KefuLangValidate())->goCheck('add');
            $result = KefuLangLogic::add($post);
            if($result){
                return JsonServer::success('新增成功', []);
            }
            return JsonServer::error('新增失败');
        }
        return view();

    }

    /**
     * @notes 编辑话术
     * @return \think\response\Json|\think\response\View
     * @author cjhao
     * @date 2021/11/29 15:59
     */
    public function edit()
    {
        if($this->request->isAjax()){
            $post= (new KefuLangValidate())->goCheck();
            $result = KefuLangLogic::edit($post);
            if($result){
                return JsonServer::success('修改成功', []);
            }
            return JsonServer::error('修改失败');
        }
        $id = $this->request->get('id');
        return view('', [
            'detail'   => KefuLangLogic::detail($id),
        ]);

    }

    /**
     * @notes 删除话术
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/29 16:35
     */
    public function del()
    {
        $post= (new KefuLangValidate())->goCheck('del');
        $result = KefuLangLogic::del($post['id']);
        if($result){
            return JsonServer::success('删除成功', []);
        }
        return JsonServer::error('删除失败');
    }
}