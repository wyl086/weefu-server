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


use app\api\logic\UserAddressLogic;
use app\api\validate\UserAddressValidate;
use app\common\basics\Api;
use app\common\server\JsonServer;
use think\exception\ValidateException;

class UserAddress extends Api
{
    public $like_not_need_login = ['handleregion'];


    //获取地址列表
    public function lists()
    {
        $user_id = $this->user_id;
        $result = UserAddressLogic::infoUserAddress($user_id);
        return JsonServer::success('获取成功', $result);
    }



    //获取一条地址详情
    public function detail()
    {
        $get = $this->request->get();
        try {
            validate(UserAddressValidate::class)->scene('one')->check($get);
            $user_id = $this->user_id;
            $result = UserAddressLogic::getOneAddress($user_id, $get);
            if ($result) {
//                return JsonServer::success('获取成功', $result);
                $data = ['code'=>1,'show'=>0,'msg' => '获取成功','data'=>$result];
                return json($data);
            }
            return JsonServer::error('获取失败');
        } catch (ValidateException $e) {
            return JsonServer::error('获取失败');
        }
    }


    //获取默认地址
    public function getDefault()
    {
        $user_id = $this->user_id;
        $result = UserAddressLogic::getDefaultAddress($user_id);
        if ($result) {
            return JsonServer::success('获取成功', $result);
        }
        return JsonServer::error('获取失败');
    }


    //设置默认地址
    public function setDefault()
    {
        $post = $this->request->post();
        try {
            validate(UserAddressValidate::class)->scene('set')->check($post);
            $user_id = $this->user_id;
            $result = UserAddressLogic::setDefaultAddress($user_id, $post);
            if ($result) {
                return JsonServer::success('设置成功');
            }
            return JsonServer::error('设置失败');
        } catch (ValidateException $e) {
            return JsonServer::error('设置失败');
        }
    }


    //添加收货地址
    public function add()
    {
        $post = $this->request->post();
        try {
            (new UserAddressValidate())->goCheck('add',$post);
            $user_id = $this->user_id;
            $result = UserAddressLogic::addUserAddress($user_id, $post);
            if ($result) {
                return JsonServer::success('添加成功');
            }
            return JsonServer::error('添加失败');
        } catch (ValidateException $e) {
            return JsonServer::error('添加失败');
        }
    }


    //更新收货地址
    public function update()
    {
        $post = $this->request->post();
        try {
            validate(UserAddressValidate::class)->scene('edit')->check($post);
            $user_id = $this->user_id;
            $result = UserAddressLogic::editUserAddress($user_id, $post);
            if ($result) {
                return JsonServer::success('修改成功');
            }
            return JsonServer::error('修改失败');
        } catch (ValidateException $e) {
            return JsonServer::error('修改失败');
        }
    }


    //删除收货地址
    public function del()
    {
        $post = $this->request->post();
        try {
            validate(UserAddressValidate::class)->scene('del')->check($post);
            $user_id = $this->user_id;
            $result = UserAddressLogic::delUserAddress($user_id, $post);
            if ($result) {
                return JsonServer::success('删除成功');
            }
            return JsonServer::error('删除失败');
        } catch (ValidateException $e) {
            return JsonServer::error('删除失败');
        }
    }


    //将省市区名称转换成省市区id
    public function handleRegion()
    {
        $post = $this->request->post();
        try {
            validate(UserAddressValidate::class)->scene('handleRegion')->check($post);
            $province = $this->request->post('province');
            $city = $this->request->post('city');
            $district = $this->request->post('district');
            $res = UserAddressLogic::handleRegion($province, $city, $district);
            if ($res) {
                return JsonServer::success('获取成功', $res);
            }
            return JsonServer::error('获取失败');
        } catch (ValidateException $e) {
            return JsonServer::error('获取失败');
        }
    }

}