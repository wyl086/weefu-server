<?php
namespace app\admin\logic\user;

use app\common\basics\Logic;
use app\common\model\user\User;
use app\common\model\user\UserLevel;
use app\common\server\UrlServer;
use think\facade\Db;

class LevelLogic extends Logic
{
    public static function lists($get)
    {
        $count = UserLevel::where(['del'=>0])->count();
        $lists = UserLevel::where(['del'=>0])->order('growth_value', 'asc')->page($get['page'], $get['limit'])->select()->toArray();

        foreach ($lists as &$item){
            $item['image'] = UrlServer::getFileUrl($item['image']);
            $item['background_image'] = UrlServer::getFileUrl($item['background_image']);
        }
        return ['count' => $count, 'lists' => $lists];
    }

    public static function add($post)
    {
        Db::startTrans();
        try{
            $userLevel = UserLevel::where(['name'=>trim($post['name']), 'del'=>0])->findOrEmpty();
            if(!$userLevel->isEmpty()) {
                throw new \think\Exception('等级名称已被使用，请更换后重试');
            }
            $userLevel = UserLevel::where(['growth_value'=>intval($post['growth_value']), 'del'=>0])->findOrEmpty();
            if(!$userLevel->isEmpty()) {
                throw new \think\Exception('指定成长值的等级已存在');
            }
            $time = time();
            $data = [
                'name' => trim($post['name']),
                'growth_value' => intval($post['growth_value']),
                'image' => clearDomain($post['image']),
                'background_image' => clearDomain($post['background_image']),
                'remark' => trim($post['remark']),
                'discount' => $post['discount'],
                'create_time' => $time,
                'update_time' => $time,
                'del' => 0
            ];
            UserLevel::create($data);
            // 更新会员等级
            $userArr = User::field('id,level,user_growth')->where('del', 0)->select()->toArray();
            self::updateUserLevel($userArr);
            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function getUserLevel($id){
        $detail = UserLevel::where(['id'=>$id,'del'=>0])->findOrEmpty();
        if($detail->isEmpty()) {
            return [];
        }
        $detail = $detail->toArray();
        $detail['image'] = UrlServer::getFileUrl($detail['image']);
        $detail['background_image'] = UrlServer::getFileUrl($detail['background_image']);
        return $detail;
    }

    public static function edit($post)
    {
        if(empty($post['discount']) || $post['discount'] === ''){
            $post['discount'] = 10;
        }
        Db::startTrans();
        try{
            $userLevel = UserLevel::where([
                ['name', '=', trim($post['name'])],
                ['del', '=', 0],
                ['id', '<>', $post['id']]
            ])->findOrEmpty();
            if(!$userLevel->isEmpty()) {
                throw new \think\Exception('等级名称已被使用，请更换后重试');
            }
            $userLevel = UserLevel::where([
                ['growth_value', '=', intval($post['growth_value'])],
                ['del', '=', 0],
                ['id', '<>', $post['id']]
            ])->findOrEmpty();
            if(!$userLevel->isEmpty()) {
                throw new \think\Exception('指定成长值的等级已存在');
            }
            $time = time();
            $data = [
                'id' => $post['id'],
                'name' => trim($post['name']),
                'growth_value' => intval($post['growth_value']),
                'image' => clearDomain($post['image']),
                'background_image' => clearDomain($post['background_image']),
                'discount' => $post['discount'],
                'remark' => trim($post['remark']),
                'update_time' => $time,
                'del' => 0
            ];
            UserLevel::update($data);
            // 更新会员等级
            $userArr = User::field('id,level,user_growth')->where('del', 0)->select()->toArray();
            self::updateUserLevel($userArr);
            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function del($id)
    {
        Db::startTrans();
        try{
            $data = [
                'id' => $id,
                'del' => 1,
                'update_time' => time()
            ];
            UserLevel::update($data);
            // 更新会员等级
            $userArr = User::field('id,level,user_growth')->where('del', 0)->select()->toArray();
            self::updateUserLevel($userArr);
            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 更新会员等级
     * 原则：只升不降
     * $userArr 需要更新会员等级的用户数组，必须的字段：id,level,user_growth
     */
    public static function updateUserLevel($userArr)
    {
        // 所有会员等级
        $userLevelArr = UserLevel::field('id,growth_value')->where('del', 0)
            ->order('growth_value', 'desc')
            ->select()
            ->toArray();
        if(empty($userLevelArr)) { // 未设置会员等级
            // 全部会员等级统一更新为0
            User::where('del', 0)->update([
                'level' => 0,
                'update_time' => time()
            ]);
        }
        // 转格式，变为id为数组下标
        $levelArr = [];
        foreach($userLevelArr as $item) {
            $levelArr[$item['id']] = $item;
        }

        $updateData = [];
        foreach($levelArr as $level) {
            $filterIds = []; // 本轮结束已处理的会员id
            foreach($userArr as $user) {
                if($user['user_growth'] >= $level['growth_value']) {
                    $targetLevelGrwothValue = $level['growth_value']; // 目标会员等级成长值
                    // 原会员等级成长值
                    $originLevelGrowthValue = isset($levelArr[$user['level']]) ? $levelArr[$user['level']]['growth_value'] : 0;
                    // 降级或等级一样,不处理直接下一轮循环
                    if($originLevelGrowthValue > $targetLevelGrwothValue) {
                        $filterIds[] = $user['id'];
                        continue;
                    }
                    $updateData[] = ['id'=>$user['id'], 'level'=>$level['id']];
                    $filterIds[] = $user['id'];
                }
            }
            // 过滤已处理过的用户,避免重复更新
            $userArr = array_filter($userArr, function($item) use ($filterIds){
                return !in_array($item['id'], $filterIds);
            });
        }

        // 剩余未处理的会员，若原有等级已被删除掉，直接置为0
        foreach($userArr as $user) {
            if(!isset($levelArr[$user['level']])) {
                $updateData[] = ['id'=>$user['id'], 'level'=>0];
            }
        }
        $userModel = new User();
        $userModel->saveAll($updateData);
    }

    public static function getLevelList()
    {
        $levelArr = UserLevel::field('id,name')
            ->where('del', 0)
            ->order('growth_value', 'asc')
            ->select()
            ->toArray();
        $levelArr[0] = ['id'=>0, 'name'=>'暂无等级'];
        return $levelArr;
    }
}