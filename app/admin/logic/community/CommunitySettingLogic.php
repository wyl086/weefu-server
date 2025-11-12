<?php


namespace app\admin\logic\community;


use app\common\basics\Logic;
use app\common\server\ConfigServer;


/**
 * 种草社区设置
 * Class CommunityTopicLogic
 * @package app\admin\logic\content
 */
class CommunitySettingLogic extends Logic
{

    /**
     * @notes 获取社区配置
     * @return array
     * @author 段誉
     * @date 2022/4/28 16:13
     */
    public static function getConfig()
    {
        $config = [
            'status' => ConfigServer::get('community', 'status', 1),
            'audit_article' => ConfigServer::get('community', 'audit_article', 1),
            'audit_comment' => ConfigServer::get('community', 'audit_comment', 1),
        ];
        return $config;
    }


    /**
     * @notes 设置社区配置
     * @param $post
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/28 16:14
     */
    public static function setConfig($post)
    {
        ConfigServer::set('community', 'status', $post['status']);
        ConfigServer::set('community', 'audit_article', $post['audit_article']);
        ConfigServer::set('community', 'audit_comment', $post['audit_comment']);
    }


}