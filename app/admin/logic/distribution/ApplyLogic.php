<?php


namespace app\admin\logic\distribution;


use app\common\basics\Logic;
use app\common\logic\DistributionLogic;
use app\common\model\distribution\Distribution;
use app\common\model\distribution\DistributionMemberApply;
use app\common\model\user\User;
use app\common\server\AreaServer;
use app\common\server\UrlServer;
use think\facade\Db;

class ApplyLogic extends Logic
{
    /**
     * @Notes: 分销申请列表
     * @Author: 张无忌
     * @param $get
     * @return array
     */
    public static function lists($get)
    {
        try {
            $where[] = ['DMA.status', '=', $get['type'] ?? 0];
            $where[] = ['U.user_delete', '=', 0];
            if (!empty($get['keyword']) and $get['keyword']) {
                switch ($get['keyword_type']) {
                    case 'sn':
                        $where[] = ['U.sn', 'like', '%'.$get['keyword'].'%'];
                        break;
                    case 'nickname':
                        $where[] = ['U.nickname', 'like', '%'.$get['keyword'].'%'];
                        break;
                    case 'mobile':
                        $where[] = ['U.mobile', '=', $get['keyword']];
                        break;
                }
            }

            $model = new DistributionMemberApply();
            $lists = $model->field(['DMA.*'])->alias('DMA')
                ->where($where)
                ->with(['user.level'])
                ->join('user U', 'U.id = DMA.user_id')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])->toArray();

            foreach ($lists['data'] as &$item) {
                if ($item['user']) {
                    $item['user']['avatar'] = UrlServer::getFileUrl($item['user']['avatar']);
                }
                $item['region'] = AreaServer::getAddress([
                    $item['province'],
                    $item['city'],
                    $item['district']]
                );
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 分销申请详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new DistributionMemberApply();
        $detail = $model->field(true)
            ->with(['user.level'])
            ->findOrEmpty($id)
            ->toArray();

        $detail['status_text'] = DistributionMemberApply::getApplyStatus($detail['status']);
        $detail['region'] = AreaServer::getAddress([
                $detail['province'],
                $detail['city'],
                $detail['district']]
        );

        return $detail;
    }

    /**
     * @Notes: 审核分销申请
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function audit($post)
    {
        Db::startTrans();
        try {
            if ($post['audit_status'] == 1) {
                // 审核通过
                $model = new DistributionMemberApply();
                $apply = $model->field(true)->findOrEmpty((int)$post['id'])->toArray();

                DistributionMemberApply::update([
                    'status'        => $post['audit_status'],
                    'denial_reason' => $post['denial_reason'] ?? '',
                    'update_time'   => time()
                ], ['id'=>(int)$post['id']]);

                $distribution = Distribution::where('user_id', $apply['user_id'])->findOrEmpty()->toArray();
                if (empty($distribution)) {
                    // 生成分销基础信息表
                    DistributionLogic::add($apply['user_id']);
                }
                // 更新分销基础信息表
                Distribution::where('user_id', $apply['user_id'])->update([
                    'is_distribution' => 1,
                    'distribution_time' => time()
                ]);
            } else {
                // 审核拒绝
                DistributionMemberApply::update([
                    'status'        => $post['audit_status'],
                    'denial_reason' => $post['denial_reason'] ?? '',
                    'update_time'   => time()
                ], ['id'=>(int)$post['id']]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }
}