<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic;

use app\common\basics\Logic;
use app\common\model\Agent;
use app\common\model\shop\Shop;
use app\common\model\user\User;
use app\common\model\dev\DevRegion;

class AgentLogic extends Logic
{
    /**
     * Notes: 列表
     * @param $get
     * @return array
     * @author 段誉
     * @date 2024/01/01
     */
    public static function lists($get)
    {
        $where[] = ['del', '=', '0'];

        // 关键词搜索（手机号）
        if (isset($get['keyword']) && $get['keyword']) {
            $where[] = ['mobile', 'like', '%' . trim($get['keyword']) . '%'];
        }

        // 邀请码搜索
        if (isset($get['invite_code']) && $get['invite_code']) {
            $where[] = ['invite_code', 'like', '%' . trim($get['invite_code']) . '%'];
        }

        // 来源筛选
        if (isset($get['source']) && $get['source'] !== '') {
            $where[] = ['source', '=', $get['source']];
        }

        // 状态筛选
        if (isset($get['status']) && $get['status'] !== '') {
            $where[] = ['status', '=', $get['status']];
        }

        // 市级代理筛选
        if (isset($get['is_city_agent']) && $get['is_city_agent'] !== '') {
            $where[] = ['is_city_agent', '=', $get['is_city_agent']];
        }

        // 区级代理筛选
        if (isset($get['is_district_agent']) && $get['is_district_agent'] !== '') {
            $where[] = ['is_district_agent', '=', $get['is_district_agent']];
        }

        // 服务商筛选
        if (isset($get['is_service']) && $get['is_service'] !== '') {
            $where[] = ['is_service', '=', $get['is_service']];
        }

        // 推广员筛选
        if (isset($get['is_promoter']) && $get['is_promoter'] !== '') {
            $where[] = ['is_promoter', '=', $get['is_promoter']];
        }

        // 省份筛选
        if (isset($get['province_id']) && $get['province_id']) {
            $where[] = ['province_id', '=', $get['province_id']];
        }

        // 城市筛选
        if (isset($get['city_id']) && $get['city_id']) {
            $where[] = ['city_id', '=', $get['city_id']];
        }

        // 区域筛选
        if (isset($get['district_id']) && $get['district_id']) {
            $where[] = ['district_id', '=', $get['district_id']];
        }

        // 创建时间筛选
        if (isset($get['start_time']) && $get['start_time'] != '') {
            $where[] = ['create_time', '>=', strtotime($get['start_time'])];
        }
        if (isset($get['end_time']) && $get['end_time'] != '') {
            $where[] = ['create_time', '<=', strtotime($get['end_time'])];
        }

        $count = Agent::where($where)->count();

        $lists = Agent::where($where)
            ->field('id,pid,invite_code,source,source_id,mobile,province_id,city_id,district_id,is_city_agent,is_district_agent,is_service,is_promoter,status,remark,create_time,update_time')
            ->page($get['page'], $get['limit'])
            ->order('id desc')
            ->select()
            ->toArray();

        // 获取商户和用户信息
        $shopIds = [];
        $userIds = [];
        foreach ($lists as $item) {
            if ($item['source'] == 1) {
                $shopIds[] = $item['source_id'];
            } else {
                $userIds[] = $item['source_id'];
            }
        }

        $shops = [];
        if (!empty($shopIds)) {
            $shops = Shop::where('id', 'in', array_unique($shopIds))
                ->column('name', 'id');
        }

        $users = [];
        if (!empty($userIds)) {
            $users = User::where('id', 'in', array_unique($userIds))
                ->column('nickname', 'id');
        }

        // 获取所有城市ID和区域ID
        $cityIds = [];
        $districtIds = [];
        $recommenderIds = []; // 推荐人ID列表
        foreach ($lists as $item) {
            if (!empty($item['city_id'])) {
                $cityIds[] = $item['city_id'];
            }
            if (!empty($item['district_id'])) {
                $districtIds[] = $item['district_id'];
            }
            // 收集推荐人ID（pid > 0）
            if (!empty($item['pid']) && $item['pid'] > 0) {
                $recommenderIds[] = $item['pid'];
            }
        }

        // 批量获取城市名称
        $cityNames = [];
        if (!empty($cityIds)) {
            $cityNames = DevRegion::where('id', 'in', array_unique($cityIds))
                ->column('name', 'id');
        }

        // 批量获取区域名称
        $districtNames = [];
        if (!empty($districtIds)) {
            $districtNames = DevRegion::where('id', 'in', array_unique($districtIds))
                ->column('name', 'id');
        }

        // 批量获取推荐人信息
        $recommenders = [];
        if (!empty($recommenderIds)) {
            $recommenderList = Agent::where('id', 'in', array_unique($recommenderIds))
                ->where('del', 0)
                ->field('id,mobile')
                ->select()
                ->toArray();
            foreach ($recommenderList as $recommender) {
                $recommenders[$recommender['id']] = $recommender;
            }
        }

        foreach ($lists as &$item) {
            // 来源描述
            $item['source_desc'] = $item['source'] == 1 ? '商户' : '用户';
            
            // 来源名称
            if ($item['source'] == 1) {
                $item['source_name'] = isset($shops[$item['source_id']]) ? $shops[$item['source_id']] : '未知商户';
            } else {
                $item['source_name'] = isset($users[$item['source_id']]) ? $users[$item['source_id']] : '未知用户';
            }

            // 城市名称（0值或空值显示为空字符串，前端会显示为"-"）
            $item['city_name'] = '';
            if (!empty($item['city_id']) && $item['city_id'] > 0 && isset($cityNames[$item['city_id']])) {
                $item['city_name'] = $cityNames[$item['city_id']];
            }

            // 区域名称（0值或空值显示为空字符串，前端会显示为"-"）
            $item['district_name'] = '';
            if (!empty($item['district_id']) && $item['district_id'] > 0 && isset($districtNames[$item['district_id']])) {
                $item['district_name'] = $districtNames[$item['district_id']];
            }

            // 推荐人信息
            $item['recommender_id'] = '';
            $item['recommender_mobile'] = '';
            if (!empty($item['pid']) && $item['pid'] > 0 && isset($recommenders[$item['pid']])) {
                $item['recommender_id'] = $recommenders[$item['pid']]['id'];
                $item['recommender_mobile'] = $recommenders[$item['pid']]['mobile'];
            }
        }

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }

    /**
     * Notes: 详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2024/01/01
     */
    public static function detail($id)
    {
        $detail = Agent::where(['id' => $id, 'del' => 0])->findOrEmpty()->toArray();

        if (!empty($detail)) {
            // 获取来源信息
            if ($detail['source'] == 1) {
                $shop = Shop::where('id', $detail['source_id'])->findOrEmpty();
                $detail['source_info'] = $shop->toArray();
            } else {
                $user = User::where('id', $detail['source_id'])->findOrEmpty();
                $detail['source_info'] = $user->toArray();
            }
        }

        return $detail;
    }

    /**
     * Notes: 添加
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2024/01/01
     */
    public static function add($post)
    {
        $data = [
            'pid' => isset($post['pid']) && $post['pid'] > 0 ? $post['pid'] : 1,
            'invite_code' => generate_agent_invite_code(), // 生成全局唯一邀请码
            'source' => $post['source'],
            'source_id' => $post['source_id'],
            'mobile' => $post['mobile'],
            'province_id' => $post['province_id'] ?? 0,
            'city_id' => $post['city_id'] ?? 0,
            'district_id' => $post['district_id'] ?? 0,
            'is_city_agent' => isset($post['is_city_agent']) ? $post['is_city_agent'] : 0,
            'is_district_agent' => isset($post['is_district_agent']) ? $post['is_district_agent'] : 0,
            'is_service' => isset($post['is_service']) ? $post['is_service'] : 0,
            'is_promoter' => isset($post['is_promoter']) ? $post['is_promoter'] : 0,
            'status' => isset($post['status']) ? $post['status'] : 1,
            'remark' => $post['remark'] ?? '',
            'create_time' => time(),
            'update_time' => time(),
        ];

        return Agent::insert($data);
    }

    /**
     * Notes: 编辑
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2024/01/01
     */
    public static function edit($post)
    {
        $data = [
            'pid' => isset($post['pid']) && $post['pid'] > 0 ? $post['pid'] : 1,
            'source' => $post['source'],
            'source_id' => $post['source_id'],
            'mobile' => $post['mobile'],
            'province_id' => $post['province_id'] ?? 0,
            'city_id' => $post['city_id'] ?? 0,
            'district_id' => $post['district_id'] ?? 0,
            'is_city_agent' => isset($post['is_city_agent']) ? $post['is_city_agent'] : 0,
            'is_district_agent' => isset($post['is_district_agent']) ? $post['is_district_agent'] : 0,
            'is_service' => isset($post['is_service']) ? $post['is_service'] : 0,
            'is_promoter' => isset($post['is_promoter']) ? $post['is_promoter'] : 0,
            'status' => isset($post['status']) ? $post['status'] : 1,
            'remark' => $post['remark'] ?? '',
            'update_time' => time(),
        ];

        return Agent::where(['id' => $post['id']])->update($data);
    }

    /**
     * Notes: 删除
     * @param $ids
     * @return bool
     * @author 段誉
     * @date 2024/01/01
     */
    public static function del($ids)
    {
        return Agent::where('id', 'in', $ids)->update(['del' => time()]);
    }

    /**
     * Notes: 设置状态
     * @param $post
     * @return bool
     * @author 段誉
     * @date 2024/01/01
     */
    public static function setStatus($post)
    {
        return Agent::where('id', 'in', $post['ids'])->update([
            'status' => $post['status'],
            'update_time' => time()
        ]);
    }
}


