<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\finance;

use app\common\basics\AdminBase;
use app\common\model\AccountLog;
use app\common\server\JsonServer;
use app\admin\logic\finance\WithdrawLogic;
use app\common\model\order\Order as OrderModel;
use app\common\model\Client_;
use app\common\enum\PayEnum;

/**
 * 财务-会员相关
 * Class User
 * @package app\admin\controller\finance
 */
class User extends AdminBase
{
    /**
     * @notes 会员佣金提现列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 7:01 下午
     */
    public function withdrawal()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $data = WithdrawLogic::lists($get);
            return JsonServer::success('', $data, 1);
        }
        $today = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", time()))),
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", time())) + 86399)
        ];

        $yesterday = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-1 day")))),
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-1 day"))) + 86399)
        ];


        $days_ago7 = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-7 day")))),
            date('Y-m-d 23:59:59', time())
        ];

        $days_ago30 = [
            date('Y-m-d 00:00:00', strtotime("-30 day")),
            date('Y-m-d 23:59:59', time())
        ];
        $summary = WithdrawLogic::summary();
        return view('', [
            'today' => $today,
            'yesterday' => $yesterday,
            'days_ago7' => $days_ago7,
            'days_ago30' => $days_ago30,
            'summary' => $summary
        ]);
    }

    /**
     * @notes 会员佣金明细列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 7:01 下午
     */
    public function commission()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $data = WithdrawLogic::commission($get);
            return JsonServer::success('', $data, 1);
        }
        $today = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", time()))),
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", time())) + 86399)
        ];

        $yesterday = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-1 day")))),
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-1 day"))) + 86399)
        ];


        $days_ago7 = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-7 day")))),
            date('Y-m-d 23:59:59', time())
        ];

        $days_ago30 = [
            date('Y-m-d 00:00:00', strtotime("-30 day")),
            date('Y-m-d 23:59:59', time())
        ];
        return view('', [
            'today' => $today,
            'yesterday' => $yesterday,
            'days_ago7' => $days_ago7,
            'days_ago30' => $days_ago30,
            'source_type' => AccountLog::getEarningsChange()
        ]);
    }

    /**
     * @notes 充值明细列表
     * @return \think\response\Json|\think\response\View
     * @author suny
     * @date 2021/7/13 7:01 下午
     */
    public function recharge()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $data = WithdrawLogic::recharge($get);
            return JsonServer::success('', $data, 1);
        }
        // 订单状态
        $order_status = OrderModel::getOrderStatus(true);
        // 订单类型
        $order_type = OrderModel::getOrderType(true);
        // 订单来源
        $order_source = Client_::getClient(true);
        unset($order_source[2]);
        // 支付方式
        $pay_way = PayEnum::getPayWay(true);
        unset($pay_way[3], $pay_way[4]);
        // 配送方式
        $today = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", time()))),
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", time())) + 86399)
        ];

        $yesterday = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-1 day")))),
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-1 day"))) + 86399)
        ];


        $days_ago7 = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-7 day")))),
            date('Y-m-d 23:59:59', time())
        ];

        $days_ago30 = [
            date('Y-m-d 00:00:00', strtotime("-30 day")),
            date('Y-m-d 23:59:59', time())
        ];
        return view('', [
            'order_status' => $order_status,
            'order_type' => $order_type,
            'order_source' => $order_source,
            'pay_way' => $pay_way,
            'today' => $today,
            'yesterday' => $yesterday,
            'days_ago7' => $days_ago7,
            'days_ago30' => $days_ago30,
        ]);
    }


    /**
     * @notes 账户明细列表
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function account()
    {

        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $data = WithdrawLogic::account($get);
            return JsonServer::success('', $data, 1);
        }
        $today = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", time()))),
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", time())) + 86399)
        ];

        $yesterday = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-1 day")))),
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-1 day"))) + 86399)
        ];


        $days_ago7 = [
            date('Y-m-d H:i:s', strtotime(date("Y-m-d", strtotime("-7 day")))),
            date('Y-m-d 23:59:59', time())
        ];

        $days_ago30 = [
            date('Y-m-d 00:00:00', strtotime("-30 day")),
            date('Y-m-d 23:59:59', time())
        ];
        return view('', [
            'today' => $today,
            'yesterday' => $yesterday,
            'days_ago7' => $days_ago7,
            'days_ago30' => $days_ago30,
        ]);
    }

    /**
     * @notes 会员佣金提现详情
     * @return \think\response\View
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function withdraw_detail()
    {

        $id = $this->request->get('id', '', 'intval');
        $detail = WithdrawLogic::detail($id);
        return view('detail', [
            'detail' => $detail
        ]);
    }

    /**
     * @notes 显示提现审核界面
     * @return \think\response\View
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function withdraw_review()
    {

        $id = $this->request->get('id', '', 'intval');
        return view('review', [
            'id' => $id
        ]);
    }

    /**
     * @notes 审核通过
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function confirm()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $result = WithdrawLogic::confirm($post);
            if ($result['code']) {
                return JsonServer::success($result['msg']);
            } else {
                return JsonServer::error($result['msg']);
            }
        }
    }

    /**
     * @notes 审核拒绝
     * @return \think\response\Json
     * @throws \think\exception\PDOException
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function refuse()
    {

        if ($this->request->isAjax()) {
            $post = $this->request->post();
            WithdrawLogic::refuse($post);
            return JsonServer::success('已拒绝提现');
        }
    }

    /**
     * @notes 显示提现转账界面
     * @return \think\response\View
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function transfer()
    {

        $id = $this->request->get('id', '', 'intval');
        return view('', [
            'id' => $id
        ]);
    }

    /**
     * @notes 转账失败
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function transferFail()
    {

        $post = $this->request->post();
        $result = WithdrawLogic::transferFail($post);
        if ($result['code']) {
            return JsonServer::success($result['msg']);
        } else {
            return JsonServer::error($result['msg']);
        }
    }

    /**
     * @notes 转账成功
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function transferSuccess()
    {

        $post = $this->request->post();
        $result = WithdrawLogic::transferSuccess($post);
        if ($result['code']) {
            return JsonServer::success($result['msg']);
        } else {
            return JsonServer::error($result['msg']);
        }
    }

    /**
     * @notes 提现结果查询
     * @return \think\response\Json
     * @author suny
     * @date 2021/7/13 7:02 下午
     */
    public function search()
    {

        $id = $this->request->post('id', '', 'intval');
        $result = WithdrawLogic::search($id);
        if ($result['code']) {
            return JsonServer::success($result['msg']);
        } else {
            return JsonServer::error($result['msg']);
        }
    }

    /**
     * @notes 提现失败
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 7:03 下午
     */
    public function withdrawFailed()
    {

        $id = $this->request->post('id', '', 'intval');
        WithdrawLogic::withdrawFailed($id);
        return JsonServer::success('提现失败已回退佣金');
    }


    /**
     * @notes 导出充值明细Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function rechargeExport()
    {
        $params = $this->request->get();
        $result = WithdrawLogic::recharge($params, true);
        if(false === $result) {
            return JsonServer::error(WithdrawLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }


    /**
     * @notes 导出账户明细Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function accountExport()
    {
        $params = $this->request->get();
        $result = WithdrawLogic::account($params, true);
        if(false === $result) {
            return JsonServer::error(WithdrawLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }


    /**
     * @notes 导出佣金明细Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function commissionExport()
    {
        $params = $this->request->get();
        $result = WithdrawLogic::commission($params, true);
        if(false === $result) {
            return JsonServer::error(WithdrawLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }



    /**
     * @notes 导出佣金提现Excel
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/24 10:20
     */
    public function withdrawalExport()
    {
        $params = $this->request->get();
        $result = WithdrawLogic::lists($params, true);
        if(false === $result) {
            return JsonServer::error(WithdrawLogic::getError() ?: '导出失败');
        }
        return JsonServer::success('', $result);
    }
}