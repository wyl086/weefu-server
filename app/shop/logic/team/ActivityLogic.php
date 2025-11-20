<?php


namespace app\shop\logic\team;


use app\common\basics\Logic;
use app\common\enum\GoodsEnum;
use app\common\enum\OrderEnum;
use app\common\enum\OrderLogEnum;
use app\common\enum\TeamEnum;
use app\common\logic\OrderRefundLogic;
use app\common\model\bargain\Bargain;
use app\common\model\goods\Goods;
use app\common\model\order\Order;
use app\common\model\seckill\SeckillGoods;
use app\common\model\team\TeamActivity;
use app\common\model\team\TeamFound;
use app\common\model\team\TeamGoods;
use app\common\model\team\TeamJoin;
use Exception;
use think\facade\Db;

class ActivityLogic extends Logic
{
    /**
     * @Notes: 获取拼团活动
     * @Author: 张无忌
     * @param $get
     * @param $shop_id
     * @return array|bool
     */
    public static function lists($get, $shop_id)
    {
        try {

            $where[] = ['T.shop_id', '=', $shop_id];
            $where[] = ['T.del', '=', 0];
            if (!empty($get['datetime']) and $get['datetime']) {
                list($start, $end) = explode(' - ', $get['datetime']);
                $where[] = ['T.create_time', '>=', strtotime($start.' 00:00:00')];
                $where[] = ['T.create_time', '<=', strtotime($end.' 23:59:59')];
            }

            if (!empty($get['name']) and $get['name']) {
                $where[] = ['G.name', 'like', '%'.$get['name'].'%'];
            }

            if (isset($get['status']) and is_numeric($get['status'])) {
                $where[] = ['T.status', '=', intval($get['status'])];
            }

            if (isset($get['type']) and $get['type']) {
                $where[] = ['T.audit', '=', $get['type']-1];
            }

            $model = new TeamActivity();
            $lists = $model->alias('T')->field(['T.*'])
                ->where($where)
                ->with(['goods'])
                ->join('goods G', 'G.id = T.goods_id')
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
     * @Notes: 选择拼团商品
     * @Author: 张无忌
     * @param $get
     * @param $shop_id
     * @return array
     */
    public static function select($get, $shop_id)
    {
        try {
            $where = [];
            if (!empty($get['name']) and $get['name']) {
                $where[] = ['name', 'like', '%'.$get['name'].'%'];
            }


            $model = (new Goods());
            $lists = $model->field(['id,name,image,stock,max_price,min_price,market_price'])->where([
                ['shop_id', '=', $shop_id],
                ['audit_status', '=', 1],
                ['del', '=', 0],
                ['status', '=', 1],
                ['type', '=', GoodsEnum::TYPE_ACTUAL]
            ])->withAttr('is_activity', function ($value, $data) use($shop_id) {
                unset($value);
                // 是否是秒杀
                $seckill = (new SeckillGoods())->where(['goods_id'=>$data['id']])
                    ->where(['del'=>0, 'shop_id'=>$shop_id])
                    ->findOrEmpty()->toArray();
                if ($seckill) return '秒杀中';
                // 是否是砍价
                $bargain = (new Bargain())->where(['goods_id'=>$data['id']])
                    ->where('del', '=', 0)
                    ->where('shop_id', '=', $shop_id)
                    ->findOrEmpty()->toArray();
                if ($bargain) return '砍价中';

                return '正常';
            })->where($where)->with(['GoodsItem'])
              ->append(['is_activity'])
              ->paginate([
                'page' => $get['page'] ?? 1,
                'list_rows' => $get['limit'] ?? 20,
                'var_page' => 'page'
            ])->toArray();

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 数据统计
     * @Author: 张无忌
     * @param $shop_id
     * @return mixed
     */
    public static function statistics($shop_id)
    {
        $where[] = ['del', '=', 0];
        $where[] = ['shop_id', '=', $shop_id];

        $model = new TeamActivity();
        $detail['total']       = $model->where($where)->count();
        $detail['stayAudit']   = $model->where($where)->where(['audit'=>0])->count();
        $detail['adoptAudit']  = $model->where($where)->where(['audit'=>1])->count();
        $detail['refuseAudit'] = $model->where($where)->where(['audit'=>2])->count();
        return $detail;
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
     * @Notes: 新增拼团活动
     * @Author: 张无忌
     * @param $post
     * @param $shop_id
     * @return bool
     */
    public static function add($post, $shop_id)
    {
        Db::startTrans();
        try {
            $goods = (new Goods())->findOrEmpty($post['goods_id'])->toArray();
            if (!$goods) throw new \think\Exception('选择的商品不存在');

            if (strtotime($post['activity_start_time']) >= strtotime($post['activity_end_time'])) {
                throw new \think\Exception('团活动开始时间不能少于等于结束时间');
            }

            if ((int)$post['winning_people_num'] > (int)$post['people_num']) {
                throw new \think\Exception('中奖人数不能大于成团人数');
            }

            // 新增拼团活动信息
            $team = TeamActivity::create([
                'shop_id'        => $shop_id,
                'goods_id'       => $post['goods_id'],
                'people_num'     => $post['people_num'],
                'winning_people_num' => $post['winning_people_num'],
                'share_title'    => $post['share_title'] ?? '',
                'share_intro'    => $post['share_intro'] ?? '',
                'team_max_price' => self::getMaxOrMinPrice($post)['max'],
                'team_min_price' => self::getMaxOrMinPrice($post)['min'],
                'audit'          => 0,
                'del'            => 0,
                'status'         => $post['status'],
                'effective_time' => $post['effective_time'],
                'activity_start_time' => strtotime($post['activity_start_time']),
                'activity_end_time'   => strtotime($post['activity_end_time']),
                'create_time'         => time()
            ]);

            // 新增拼团商品规格
            $lists = [];
            foreach ($post['item'] as $key => $value) {
                foreach ($value as $K => $item) {
                    $lists[] = [
                        'team_id'    => $team['id'],
                        'goods_id'   => $key,
                        'item_id'    => $K,
                        'team_price' => $item
                    ];
                }
            }
            if (!empty($lists)) (new TeamGoods())->insertAll($lists);

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 编辑拼团活动
     * @Author: 张无忌
     * @param $post
     * @param $shop_id
     * @return bool
     */
    public static function edit($post, $shop_id)
    {
        Db::startTrans();
        try {
            $goods = (new Goods())->findOrEmpty($post['goods_id'])->toArray();
            if (!$goods) throw new \think\Exception('选择的商品不存在');

            if (strtotime($post['activity_start_time']) >= strtotime($post['activity_end_time'])) {
                throw new \think\Exception('团活动开始时间不能少于等于结束时间');
            }

            $activity = (new TeamActivity())->findOrEmpty($post['id'])->toArray();
            $audit = $activity['audit'] != 1 ? 0 : 1;

            if ((int)$post['winning_people_num'] > (int)$post['people_num']) {
                throw new \think\Exception('中奖人数不能大于成团人数');
            }


            // 编辑拼团活动信息
            TeamActivity::update([
                'shop_id'             => $shop_id,
                'goods_id'            => $post['goods_id'],
                'people_num'          => $post['people_num'],
                'winning_people_num'  => $post['winning_people_num'],
                'share_title'         => $post['share_title'] ?? '',
                'share_intro'         => $post['share_intro'] ?? '',
                'team_max_price'      => self::getMaxOrMinPrice($post)['max'],
                'team_min_price'      => self::getMaxOrMinPrice($post)['min'],
                'status'              => $post['status'],
                'audit'               => $audit,
                'effective_time'      => $post['effective_time'],
                'activity_start_time' => strtotime($post['activity_start_time']),
                'activity_end_time'   => strtotime($post['activity_end_time']),
            ], ['id'=>$post['id']]);

            // 删除旧的拼团商品
            (new TeamGoods())->where(['team_id'=>$post['id']])->delete();

            // 更新拼团商品规格
            $lists = [];
            foreach ($post['item'] as $key => $value) {
                foreach ($value as $K => $item) {
                    $lists[] = [
                        'team_id'    => $post['id'],
                        'goods_id'   => $key,
                        'item_id'    => $K,
                        'team_price' => $item
                    ];
                }
            }
            if (!empty($lists)) (new TeamGoods())->insertAll($lists);

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 删除拼团活动
     * @Author: 张无忌
     * @param $id
     * @return bool
     */
    public static function del($id)
    {
        try {
            TeamActivity::update([
                'del' => 1,
                'update_time' => time()
            ], ['id'=>$id]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 停止拼团活动
     * @Author: 张无忌
     * @param $id
     * @return bool
     */
    public static function stop($id)
    {
        try {
            TeamActivity::update([
                'status' => 0,
                'update_time' => time()
            ], ['id'=>$id]);

            $team_ids = (new TeamFound())->where(['team_activity_id'=>$id, 'status'=>0])->column('id');

            $teamJoin =  (new TeamJoin())->alias('TJ')
                ->field(['TJ.*,O.order_sn,O.order_status,O.pay_status,O.refund_status,O.order_amount'])
                ->where('team_id', 'in', $team_ids)
                ->join('order O', 'O.id=TJ.order_id')
                ->select()->toArray();

            self::teamFail($teamJoin, $team_ids, time());

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 开启拼团活动
     * @Author: 张无忌
     * @param $id
     * @return bool
     */
    public static function open($id)
    {
        try {
            TeamActivity::update([
                'status' => 1,
                'update_time' => time()
            ], ['id'=>$id]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 查看最低活动价和最高活动价
     * @Author: 张无忌
     * @param $post
     * @return array
     */
    private static function getMaxOrMinPrice($post)
    {
        $team_price = [];
        foreach ($post['item'] as $key => $value) {
            foreach ($value as $K => $item) {
                array_push($team_price, $item);
            }
        }
        $team_max_price = !empty($team_price) ? max($team_price) : 0;
        $team_min_price = !empty($team_price) ? min($team_price) : 0;

        return [
            'max' => $team_max_price,
            'min' => $team_min_price
        ];
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