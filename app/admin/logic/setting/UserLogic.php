<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic\setting;

use app\common\basics\Logic;
use app\common\server\ConfigServer;
use app\common\server\FileServer;
use app\common\server\UrlServer;
use think\facade\Db;

/**
 * 用户设置逻辑层
 * Class UserLogic
 * @package app\admin\logic\setting
 */
class UserLogic extends Logic
{
    /**
     * @notes 获取用户配置
     * @return array
     * @author Tab
     * @date 2021/9/1 10:07
     */
    public static function getConfig()
    {
        $config = [
            // 邀请下级 0-关闭 1-开启(默认)
            'is_open' => ConfigServer::get('invite', 'is_open', 1),
            // 邀请下级资格 1-全部用户(默认) 2-分销会员
            'qualifications' => ConfigServer::get('invite', 'qualifications', [1]),
            // 成为下级条件 1-邀请码(默认)
            'condition' => ConfigServer::get('invite', 'condition', 1),
            // 自定义邀请海报
            'poster' => ConfigServer::get('invite', 'poster', '/images/share/share_user_bg.png'),
            //指定会员
            'invite_appoint_user' => ConfigServer::get('invite', 'invite_appoint_user', []),
        ];
        $config['poster'] = empty($config['poster']) ? $config['poster'] : UrlServer::getFileUrl($config['poster']);
        return $config;
    }

    /**
     * @notes 用户设置
     * @param $params
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/9/1 10:33
     */
    public static function set($params)
    {
        try {
            if(!isset($params['poster'])) {
                throw new \Exception('请选择自定义海报');
            }
            if(!isset($params['qualifications'])) {
                throw new \Exception('请至少选择一种分销资格');
            }
            //兼容以前版本,保存数据格式
            $params['qualifications'] = [$params['qualifications']];
            if(count($params['qualifications']) >= 2){
                throw new \Exception('分销资格只能选择一种');
            }
            $allowFields = ['is_open', 'qualifications', 'condition', 'poster','invite_appoint_user'];
            if(in_array(2,$params['qualifications'])){
                if(!isset($params['invite_appoint_user']) || empty($params['invite_appoint_user'])){
                    throw new \Exception('请选择指定会员等级');
                }
                $user_level = self::getUserLevel();
                $user_level = array_column($user_level,'id');
                $ids = [];
                foreach ($params['invite_appoint_user'] as $id =>$val) {
                    if(!in_array($id,$user_level)){
                        throw new \Exception('用户等级错误，请刷新页面');
                    }
                    $ids[] = $id;
                }
                $params['invite_appoint_user'] = $ids;
            }else{

                $params['invite_appoint_user'] = [];

            }
            foreach ($allowFields as $field) {
                if(isset($params[$field])) {
                    $params[$field] = is_array($params[$field]) ? json_encode($params[$field], JSON_UNESCAPED_UNICODE) : $params[$field];
                    $params[$field] = $field == 'poster' ? UrlServer::setFileUrl($params[$field]) : $params[$field];
                    ConfigServer::set('invite', $field, $params[$field]);
                }
            }
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2022/2/28 10:34
     */
    public static function getUserLevel(){
        $user_level = Db::name('user_level')
            ->where(['del'=>0])
            ->field('id,name')
            ->select()->toArray();
        return $user_level;
    }
}