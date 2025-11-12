<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\shop\controller\kefu;
use app\shop\logic\kefu\KefuLangLogic;
use app\shop\validate\kefu\KefuLangValidate;
use app\common\basics\ShopBase;
use app\common\server\JsonServer;

/**
 * 客服术语
 * Class KefuLang
 * @package app\admin\controller\kefu
 */
class KefuLang extends ShopBase
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
            $lists = KefuLangLogic::lists($this->shop_id,$limit,$page);
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
            $post= (new KefuLangValidate())->goCheck('add',['shop_id'=>$this->shop_id]);
            $result = KefuLangLogic::add($this->shop_id,$post);
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
            $post= (new KefuLangValidate())->goCheck(null,['shop_id'=>$this->shop_id]);
            $result = KefuLangLogic::edit($post);
            if($result){
                return JsonServer::success('修改成功', []);
            }
            return JsonServer::error('修改失败');
        }
        $id = $this->request->get('id');
        return view('', [
            'detail'   => KefuLangLogic::detail($this->shop_id,$id),
        ]);

    }

    /**
     * @notes 删除话术
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/29 16:46
     */
    public function del()
    {
        $post= (new KefuLangValidate())->goCheck('del',['shop_id'=>$this->shop_id]);
        $result = KefuLangLogic::del($this->shop_id,$post['id']);
        if($result){
            return JsonServer::success('删除成功', []);
        }
        return JsonServer::error('删除失败');
    }
}