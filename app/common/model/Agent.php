<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model;

use app\common\basics\Models;

/**
 * 代理模型
 * Class Agent
 * @package app\common\model
 */
class Agent extends Models
{
    protected $name = 'agent';

    /**
     * 获取来源描述
     * @param $value
     * @param $data
     * @return string
     */
    public function getSourceDescAttr($value, $data)
    {
        $sourceMap = [
            1 => '商户',
            2 => '用户'
        ];
        return isset($sourceMap[$data['source']]) ? $sourceMap[$data['source']] : '未知';
    }

    /**
     * 获取状态描述
     * @param $value
     * @param $data
     * @return string
     */
    public function getStatusDescAttr($value, $data)
    {
        return $data['status'] == 1 ? '启用' : '禁用';
    }

    /**
     * 获取创建时间
     * @param $value
     * @return false|string
     */
    public function getCreateTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 获取更新时间
     * @param $value
     * @return false|string
     */
    public function getUpdateTimeAttr($value)
    {
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
}


