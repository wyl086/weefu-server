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
namespace app\shopapi\controller;
use app\common\basics\ShopApi;
use app\common\server\JsonServer;
use app\shop\validate\BankValidate;
use app\shopapi\{
    logic\ShopLogic,
    validate\ShopDataSetValidate,
    validate\ShopWithdrawValidate,
    validate\AdminPasswordValidate
};


/**
 * 商家控制器
 * Class Shop
 * @package app\shopapi\controller
 */
class Shop extends ShopApi{


    /**
     * @notes 获取商家可提现余额
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/10 16:16
     */
    public function getShopInfo(){
        $shop = (new ShopLogic)->getShopInfo($this->shop_id);
        return JsonServer::success('', $shop);
    }

    /**
     * @notes 获取提现信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/11/10 16:30
     */
    public function getWithdrawInfo(){
        $result = (new ShopLogic)->getWithdrawInfo($this->shop_id);
        return JsonServer::success('', $result);
    }




    /**
     * @notes 提现操作
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/10 17:03
     */
    public function withdraw(){
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new ShopWithdrawValidate())->goCheck('',$post);
        $result = (new ShopLogic)->withdraw($post);
        if(true === $result){
            return JsonServer::success('提现成功');
        }
        return JsonServer::error($result );
    }

    /**
     * @notes 提现记录
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     * @author cjhao
     * @date 2021/11/10 17:11
     */
    public function withdrawLog(){
        $list = (new ShopLogic)->withdrawLog($this->shop_id,$this->page_no,$this->page_size);
        return JsonServer::success('', $list);
    }

    /**
     * @notes 添加账户
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     * @author cjhao
     * @date 2021/11/10 18:26
     */
    public function addBank(){
        (new BankValidate())->goCheck('add');
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new ShopLogic)->addBank($post);
        return JsonServer::success('添加成功');
    }

    /**
     * @notes 获取银行卡
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/11 15:47
     */
    public function getBank(){
        (new BankValidate())->goCheck('id');
        $id= $this->request->get('id');
        $data= (new ShopLogic)->getBank($id,$this->shop_id);
        return JsonServer::success('',$data);
    }

    /**
     * @notes 编辑银行卡
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/10 18:40
     */
    public function editBank(){
        (new BankValidate())->goCheck('edit');
        $post = $this->request->post();
        $post['shop_id'] = $this->shop_id;
        (new ShopLogic)->editBank($post);
        return JsonServer::success('编辑成功');
    }

    /**
     * @notes 删除银行卡
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/10 18:42
     */
    public function delBank(){
        (new BankValidate())->goCheck('id');
        $id = $this->request->post('id');
        (new ShopLogic)->delBank($id,$this->shop_id);
        return JsonServer::success('删除成功');
    }


    /**
     * @notes 设置商家信息
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/11 10:40
     */
    public function shopSet(){
        $post = $this->request->post();
        (new ShopDataSetValidate())->goCheck('',['dataset'=>$post]);
        (new ShopLogic)->shopSet($post,$this->shop_id);
        return JsonServer::success('设置成功');

    }

    /**
     * @notes 修改密码接口
     * @return \think\response\Json
     * @author cjhao
     * @date 2021/11/11 16:11
     */
    public function changePwd(){
        $post = $this->request->post();
        $post['admin_id'] = $this->admin_id;
        (new AdminPasswordValidate())->goCheck('', $post);
        $res = (new ShopLogic)->updatePassword($post, $this->shop_id);
        if(true === $res){
            return JsonServer::success('密码修改成功');
        }
        return JsonServer::error($res);
    }


}