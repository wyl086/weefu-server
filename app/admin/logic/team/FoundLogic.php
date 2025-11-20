<?php


namespace app\admin\logic\team;


use app\common\basics\Logic;
use app\common\enum\OrderEnum;
use app\common\enum\TeamEnum;
use app\common\model\order\Order;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamJoin;
use app\common\server\FileServer;
use app\common\server\UrlServer;
use Exception;

class FoundLogic extends Logic
{
    /**
     * @Notes: 开团列表
     * @Author: 张无忌
     * @param $get
     * @return array|bool
     */
    public static function lists($get)
    {
        try {
            $where = [];
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
                $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 数据统计
     * @Author: 张无忌
     * @return mixed
     */
    public static function statistics()
    {
        $model = new TeamFound();
        $detail['total']       = $model->count();
        $detail['stayStatus']   = $model->where(['status'=>0])->count();
        $detail['successStatus']  = $model->where(['status'=>1])->count();
        $detail['failStatus'] = $model->where(['status'=>2])->count();
        return $detail;
    }

    /**
     * @Notes: 拼团详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $teamFound = (new TeamFound())->alias('TF')
            ->field(['TF.*,U.sn,U.nickname,U.mobile,TA.winning_people_num'])
            ->join('user U', 'U.id = TF.user_id')
            ->join('team_activity TA', 'TA.id = TF.team_activity_id')
            ->where('TF.id', '=', intval($id))
            ->findOrEmpty()->toArray();
        $teamFound['kaituan_time'] = date('Y-m-d H:i:s', $teamFound['kaituan_time']);
        $teamFound['invalid_time'] = date('Y-m-d H:i:s', $teamFound['invalid_time']);
        $teamFound['team_end_time'] = date('Y-m-d H:i:s', $teamFound['team_end_time']);
        $teamFound['status_text'] = TeamEnum::getStatusDesc($teamFound['status']);

        return ['teamFound'=>$teamFound];
    }

    /**
     * @Notes: 参团列表
     * @Author: 张无忌
     * @param $get
     * @return array|bool
     */
    public static function join($get)
    {
        try {
            $where[] = ['TJ.team_id', '=', $get['team_id']];

            $model = new TeamJoin();
            $lists = $model->alias('TJ')->field(['TJ.*,U.sn,U.nickname,U.avatar'])
                ->join('user U', 'U.id = TJ.user_id')
                ->where($where)
                ->paginate([
                    'page'      => $get['page'] ?? 1,
                    'list_rows' => $get['limit'] ?? 20,
                    'var_page'  => 'page'
                ])->toArray();

            $orderModel = new Order();
            foreach ($lists['data'] as &$item) {
                $item['identity'] = $item['identity'] == 1 ? '团长' : '团员';

                $item['order'] = $orderModel->field([
                        'id,order_sn,order_type,order_status,
                        refund_status,pay_status,order_amount,create_time'
                    ])
                    ->with(['orderGoods'])
                    ->findOrEmpty($item['order_id'])->toArray();

                $item['order']['order_status'] = OrderEnum::getOrderStatus($item['order']['order_status']);
                $item['order']['pay_status'] = OrderEnum::getPayStatus($item['order']['pay_status']);
                $item['order']['refund_status'] = OrderEnum::getRefundStatus($item['order']['refund_status']);
                $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
}