<?php
// +----------------------------------------------------------------------
// | Multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------

namespace app\api\controller;

use app\api\logic\ShopFollowLogic;
use app\common\basics\Api;
use app\api\logic\PcLogic;
use app\common\server\JsonServer;
use app\api\validate\ChangeUserInfo;
use think\exception\ValidateException;

class Pc extends Api
{
    public $like_not_need_login = ['articleDetail','commonData','goodsList','category'];

    /**
     * @notes PC公共数据
     * @return \think\response\Json
     * @author suny
     * @date 2021/10/27 10:31 上午
     */
    public function commonData(){
        return JsonServer::success('',PcLogic::commonData($this->user_id));
    }

    /**
     * @notes 商品列表
     * @return \think\response\Json
     * @author suny
     * @date 2021/10/27 11:26 上午
     */
    public function goodsList(){
        $type = $this->request->get('type',1);
        $sort_type = $this->request->get('sort_type','');
        $sort = $this->request->get('sort','');
        $name = $this->request->get('name','');
        $category_id = $this->request->get('category_id','');
        $shop_id = $this->request->get('shop_id','');
        $list = PcLogic::goodsList($this->page_no,$this->page_size,$name,$category_id,$shop_id,$type,$sort_type,$sort);
        return JsonServer::success('',$list);
    }

    public function changeUserInfo(){
        try{
            $post = $this->request->post();
            $post['user_id'] = $this->user_id;
            validate(ChangeUserInfo::class)->check($post);
        }catch(ValidateException $e) {
            return JsonServer::error($e->getError());
        }
        $result = PcLogic::changeUserInfo($post);
        if($result === true) {
            return JsonServer::success('保存成功');
        }
        return JsonServer::error($result);
    }


    /**
     * @notes PC商品分类
     * @return \think\response\Json
     * @author heshihu
     * @date 2021/10/26 6:19 下午
     */
    public function category(){
        $cateogry = PcLogic::categoryThirdTree();
        return JsonServer::success('获取成功', $cateogry);
    }

    /**
     * @notes 文章详情
     * @return \think\response\Json
     * @author suny
     * @date 2021/10/26 6:40 下午
     */
    public function articleDetail(){
        $id = $this->request->get('id');
        return JsonServer::success('获取成功', PcLogic::articleDetail($id));
    }

    /**
     * @notes PC我的店铺收藏列表
     * @return \think\response\Json
     * @author suny
     * @date 2021/10/28 5:09 下午
     */
    public function shopFollowList()
    {
        $get = $this->request->get();
        $get['user_id'] = $this->user_id;
        $get['page_no'] = $this->page_no;
        $get['page_size'] = $this->page_size;

        $data = PcLogic::shopFollowList($get);
        return JsonServer::success('', $data);
    }
}