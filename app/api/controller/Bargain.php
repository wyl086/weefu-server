<?php

namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\BargainLogic;
use app\common\server\JsonServer;
use app\api\validate\BargainValidate;

/**
 * Class Bargain
 * @package app\api\controller
 */
class Bargain extends Api
{
    public $like_not_need_login = ['bargainNumber', 'lists', 'detail', 'closeBargain', 'test'];

    /**
     * @notes 获取砍价成功人数
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:09 下午
     */
    public function bargainNumber()
    {

        $number = BargainLogic::bargainNumber();
        $data = [
            'code' => 1,
            'show' => 0,
            'msg' => '获取成功',
            'data' => $number
        ];
        return json($data);
    }


    /**
     * @notes 砍价列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:10 下午
     */
    public function lists()
    {

        $list = BargainLogic::lists($this->page_no, $this->page_size);
        return JsonServer::success('获取成功', $list);
    }

    /**
     * @notes 砍价活动详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:10 下午
     */
    public function detail()
    {

        $get = $this->request->get();
        (new BargainValidate())->goCheck('detail', $get);
        $detail = BargainLogic::detail($get['bargain_id']);
        $data = [
            'code' => 1,
            'show' => 0,
            'msg' => '获取成功',
            'data' => $detail
        ];
        return json($data);
    }

    /**
     * @notes 发起砍价
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 6:10 下午
     */
    public function sponsor()
    {

        $post_data = $this->request->post();
        (new BargainValidate())->goCheck('sponsor', $post_data);
        $data = BargainLogic::sponsor($post_data, $this->user_id);
        if (false === $data) {
            return JsonServer::error(BargainLogic::getError());
        } else {
            return JsonServer::success('发起砍价成功', $data);
        }
    }

    /**
     * @notes 砍价助力
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:10 下午
     */
    public function knife()
    {

        $id = $this->request->post('id');
        (new BargainValidate())->goCheck('knife', ['id' => $id, 'user_id' => $this->user_id]);
        $data = BargainLogic::knife($id, $this->user_id);
        if (false === $data) {
            return JsonServer::error(BargainLogic::getError());
        } else {
            return JsonServer::success('助力成功', $data);
        }
    }

    /**
     * @notes 砍价订单列表
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:10 下午
     */
    public function orderList()
    {

        $type = $this->request->get('type', '-1');
        $list = BargainLogic::orderList($type, $this->user_id, $this->page_no, $this->page_size);
        return JsonServer::success('获取成功', $list);
    }


    /**
     * @notes 砍价详情
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:10 下午
     */
    public function bargainDetail()
    {

        $id = $this->request->get('id');
        (new BargainValidate())->goCheck('bargainDetail', ['id' => $id, 'user_id' => $this->user_id]);
        $detail = BargainLogic::bargainDetail($id, $this->user_id);
        return JsonServer::success('获取成功', $detail);
    }


    /**
     * @notes 关闭砍价订单
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 6:10 下午
     */
    public function closeBargain()
    {

        $id = $this->request->post('id');
        if ($id) {
            BargainLogic::closeBargain($id);
        }
        return JsonServer::success('关闭成功');
    }
}