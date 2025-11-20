<?php


namespace app\admin\logic\team;


use app\common\basics\Logic;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\TeamEnum;
use app\common\logic\OrderRefundLogic;
use app\common\model\order\Order;
use app\common\model\team\TeamActivity;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamJoin;
use app\common\server\UrlServer;
use Exception;
use think\facade\Db;

class ActivityLogic extends Logic
{
    /**
     * @Notes: 获取拼团活动
     * @Author: 张无忌
     * @param $get
     * @return array|bool
     */
    public static function lists($get)
    {
        try {
            $where = [];
            $where[] = ['T.del', '=', 0];
            if (!empty($get['datetime']) and $get['datetime']) {
                list($start, $end) = explode(' - ', $get['datetime']);
                $where[] = ['T.create_time', '>=', strtotime($start.' 00:00:00')];
                $where[] = ['T.create_time', '<=', strtotime($end.' 23:59:59')];
            }

            if (!empty($get['shop']) and $get['shop']) {
                $where[] = ['S.name|S.id', 'like', '%'.$get['shop'].'%'];
            }

            if (!empty($get['name']) and $get['name']) {
                $where[] = ['G.name', 'like', '%'.$get['name'].'%'];
            }

            if (!empty($get['status']) and $get['status']) {
                $where[] = ['T.status', '=', $get['status']];
            }

            if (!empty($get['type']) and $get['type']) {
                $where[] = ['T.audit', '=', $get['type']-1];
            }

            $model = new TeamActivity();
            $lists = $model->alias('T')->field(['T.*', 'S.name as shop_name,S.type as shop_type,S.logo'])
                ->where($where)
                ->with(['goods'])
                ->join('goods G', 'G.id = T.goods_id')
                ->join('shop S', 'S.id = T.shop_id')
                ->paginate([
                    'page' => $get['page'] ?? 1,
                    'list_rows' => $get['limit'] ?? 20,
                    'var_page' => 'page'
                ])->toArray();

            $teamFoundModel = new TeamFound();
            $teamJoinModel =  new TeamJoin();
            foreach ($lists['data'] as &$item) {
                $item['activity_start_time'] = date('Y-m-d H:i', $item['activity_start_time']);
                $item['activity_end_time'] = date('Y-m-d H:i', $item['activity_end_time']);
                $item['status_text'] = TeamEnum::getTeamStatusDesc($item['status']);
                $item['audit_text'] = TeamEnum::getTeamAuditDesc($item['audit']);
                $item['logo'] = UrlServer::getFileUrl($item['logo']);
                $item['shop_type'] = $item['shop_type'] == 1 ? '商家自营' : '入驻商家';

                $item['team_count'] = $teamFoundModel->where(['team_activity_id'=>$item['id']])->count();
                $item['success_found'] = $teamFoundModel->where(['status'=>1, 'team_activity_id'=>$item['id']])->count();
                $item['join_found'] = $teamJoinModel->where(['team_activity_id'=>$item['id']])->count();
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 拼团商品的开团记录
     * @param $get
     * @return array|bool
     * @author 张无忌
     * @date 2021/7/19 11:02
     */
    public static function record($get)
    {
        try {
            $where = [];
            $where[] = ['TF.shop_id', '=', (int)$get['shop_id']];
            $where[] = ['TF.team_activity_id', '=', (int)$get['team_activity_id']];
            if (isset($get['type']) and is_numeric($get['type']) and $get['type'] != 100) {
                $where[] = ['TF.status', '=', (int)$get['type']];
            }

            if (!empty($get['team_sn']) and $get['team_sn']) {
                $where[] = ['TF.team_sn', 'like', '%'.$get['team_sn'].'%'];
            }

            if (!empty($get['goods']) and $get['goods']) {
                $where[] = ['TF.goods_snap->name', 'like', '%'.$get['goods'].'%'];
            }

            if (!empty($get['datetime']) and $get['datetime']) {
                list($start, $end) = explode(' - ', $get['datetime']);
                $where[] = ['TF.kaituan_time', '>=', strtotime($start.' 00:00:00')];
                $where[] = ['TF.kaituan_time', '<=', strtotime($end.' 23:59:59')];
            }

            $model = new TeamFound();
            $lists = $model->alias('TF')->field(['TF.*,U.nickname,U.sn,U.avatar,TA.winning_people_num'])
                ->join('user U', 'U.id = TF.user_id')
                ->join('team_activity TA', 'TA.id = TF.team_activity_id')
                ->order('TF.id desc')
                ->where($where)
                ->paginate([
                    'page'      => $get['page'] ?? 1,
                    'list_rows' => $get['limit'] ?? 20,
                    'var_page'  => 'page'
                ])->toArray();

            foreach ($lists['data'] as &$item) {
                $item['peopleJoin'] = $item['people'] . '/' . $item['join'];
                $item['kaituan_time'] = date('Y-m-d H:i:s', $item['kaituan_time']);
                $item['invalid_time'] = date('Y-m-d H:i:s', $item['invalid_time']);
                $item['goods_snap']   = json_decode($item['goods_snap'], true);
                $item['status_text'] = TeamEnum::getStatusDesc($item['status']);
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 开团活动的开团记录统计
     * @param $get
     * @return mixed
     * @author 张无忌
     * @date 2021/7/19 11:05
     */
    public static function recordStatistics($get)
    {
        $where[] = ['shop_id', '=', (int)$get['shop_id']];
        $where[] = ['team_activity_id', '=', (int)$get['id']];

        $model = new TeamFound();
        $detail['total']         = $model->where($where)->count();
        $detail['stayStatus']    = $model->where($where)->where(['status'=>0])->count();
        $detail['successStatus'] = $model->where($where)->where(['status'=>1])->count();
        $detail['failStatus']    = $model->where($where)->where(['status'=>2])->count();
        return $detail;
    }


    /**
     * @Notes: 数据统计
     * @Author: 张无忌
     * @return mixed
     */
    public static function statistics()
    {
        $where[] = ['del', '=', 0];

        $model = new TeamActivity();
        $detail['total']       = $model->where($where)->count();
        $detail['stayAudit']   = $model->where($where)->where(['audit'=>0])->count();
        $detail['adoptAudit']  = $model->where($where)->where(['audit'=>1])->count();
        $detail['refuseAudit'] = $model->where($where)->where(['audit'=>2])->count();
        return $detail;
    }


    /**
     * @Notes: 审核拼团活动
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function audit($post)
    {
        try {
            if (!$post['audit'] and empty($post['explain'])) {
                throw new \think\Exception('拒绝时请填写拒绝理由');
            }

            TeamActivity::update([
                'audit' => $post['audit'],
                'update_time' => time()
            ], ['id'=>$post['id']]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 违规重审核
     * @Author: 张无忌
     * @param $id
     * @return bool
     */
    public static function violation($id)
    {
        try {
            TeamActivity::update([
                'audit'  => 2,
                'status' => 0,
                'update_time' => time()
            ], ['id' => $id]);

            $team_ids = (new TeamFound())->where(['team_activity_id' => $id, 'status' => 0])->column('id');

            $teamJoin = (new TeamJoin())->alias('TJ')
                ->field(['TJ.*,O.order_sn,O.order_status,O.pay_status,O.refund_status,O.order_amount'])
                ->where('team_id', 'in', $team_ids)
                ->join('order O', 'O.id=TJ.order_id')
                ->select()->toArray();

            self::teamFail($teamJoin, $team_ids, time());

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 拼团活动详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new TeamActivity();
        $detail = $model->field(true)
            ->with(['goods', 'teamGoods'])
            ->findOrEmpty($id)
            ->toArray();

        $detail['activity_start_time'] = date('Y-m-d H:i:s', $detail['activity_start_time']);
        $detail['activity_end_time'] = date('Y-m-d H:i:s', $detail['activity_end_time']);
        return $detail;
    }

    /**
     * @Notes: 拼团失败
     * @Author: 张无忌
     * @param $teamJoin (参团列表数据)
     * @param $found_ids
     * @param $time (时间)
     * @throws \think\Exception
     */
    private static function teamFail($teamJoin, $found_ids, $time)
    {
        Db::startTrans();
        try {
            (new TeamFound())->whereIn('id', $found_ids)
                ->update(['status'=>TeamEnum::TEAM_STATUS_FAIL, 'team_end_time'=>$time]);

            foreach ($teamJoin as $item) {
                TeamJoin::update(['status' => TeamEnum::TEAM_STATUS_FAIL, 'update_time' => $time], ['id' => $item['id']]);
                if ($item['order_status'] == OrderEnum::ORDER_STATUS_DOWN) continue;
                if ($item['refund_status'] != OrderEnum::REFUND_STATUS_NO_REFUND) continue;
                $order = (new Order())->findOrEmpty($item['order_id'])->toArray();
                // 取消订单
                OrderRefundLogic::cancelOrder($order['id'], OrderLogEnum::TYPE_SYSTEM);
                if ($order['pay_status'] == OrderEnum::PAY_STATUS_PAID) {
                    // 更新订单状态
                    OrderRefundLogic::cancelOrderRefundUpdate($order);
                    // 订单退款
                    OrderRefundLogic::refund($order, $order['order_amount'], $order['order_amount']);
                }
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            throw new \think\Exception($e->getMessage());
        }
    }
}