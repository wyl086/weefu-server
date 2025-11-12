<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\api\controller;

use app\api\logic\CartLogic;
use app\api\validate\CartValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;


/**
 * 购物车控制器
 * Class Cart
 * @package app\api\controller
 */
class Cart extends Api
{

    /**
     * @notes 购物车列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/9/7 10:23
     */
    public function lists()
    {
        $lists = CartLogic::lists($this->user_id);
        return JsonServer::success('获取成功', $lists);
    }


    /**
     * Notes: 添加
     * @author 段誉(2021/5/11 15:52)
     * @return \think\response\Json
     */
    public function add()
    {
        $post = $this->request->post();
        (new CartValidate())->goCheck('add', ['user_id' => $this->user_id]);
        $res = CartLogic::add($post, $this->user_id);
        if (false === $res) {
            $error = CartLogic::getError() ?: '系统错误';
            return JsonServer::error($error);
        }
        return JsonServer::success('添加成功');
    }


    /**
     * Notes: 更改数量
     * @author 段誉(2021/5/11 15:52)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function change()
    {
        $post = $this->request->post();
        (new CartValidate())->goCheck('change', ['user_id' => $this->user_id]);
        $res = CartLogic::change($post['cart_id'], $post['goods_num']);
        if (true === $res) {
            return JsonServer::success('');
        }
        $error = CartLogic::getError() ?: '系统错误';
        return JsonServer::error($error);
    }


    /**
     * Notes: 删除
     * @author 段誉(2021/5/11 15:52)
     * @return \think\response\Json
     */
    public function del()
    {
        $post = $this->request->post();
        (new CartValidate())->goCheck('del', ['user_id' => $this->user_id]);
        if (CartLogic::del($post['cart_id'], $this->user_id)) {
            return JsonServer::success('删除成功');
        }
        return JsonServer::error('删除失败');
    }


    /**
     * Notes: 更改选中状态
     * @author 段誉(2021/5/11 15:52)
     * @return \think\response\Json
     */
    public function selected()
    {
        $post = $this->request->post();
        (new CartValidate())->goCheck('selected', ['user_id' => $this->user_id]);
        CartLogic::selected($post, $this->user_id);
        return JsonServer::success('');
    }


    /**
     * Notes: 购物车数量
     * @author 段誉(2021/5/11 15:53)
     * @return \think\response\Json
     */
    public function num()
    {
        return JsonServer::success('', CartLogic::cartNum($this->user_id));
    }


}