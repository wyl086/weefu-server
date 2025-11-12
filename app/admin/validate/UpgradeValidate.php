<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\validate;

use app\admin\logic\system\UpgradeLogic;
use app\common\basics\Validate;
use think\facade\Cache;


class UpgradeValidate extends Validate
{

    protected $rule = [
        'version_no' => 'require|checkIsAbleUpgrade',
        'package_link' => 'require'
    ];


    protected $message = [
        'version_no.require' => '参数缺失',
        'package_link.require' => '参数缺失',
    ];


    //检查是否可以更新
    protected function checkIsAbleUpgrade($value, $reule, $data)
    {
        //验证open_basedir是否设置
        $open_basedir = ini_get('open_basedir');
        if(strpos($open_basedir, 'server') !== false) {
            return '更新失败:  请临时关闭服务器本站点的跨域攻击设置，并重启 nginx、PHP，具体参考相关升级文档';
        }

        $version_data = local_version();
        $local_version = UpgradeLogic::formatVersion($version_data['version']);
        $target_version = UpgradeLogic::formatVersion($value);

        //检查一分钟内是否多次操作
        $checkIsRepeat = UpgradeLogic::getUpgradeLock($target_version);
        if ($checkIsRepeat) {
            return '正在执行更新，请1分钟后重试……';
        } else {
            UpgradeLogic::setUpgradeLock($target_version);
        }

        //本地版本需要小于当前选中版本
        if ($local_version > $target_version) {
            return '当前系统无法升级到该版本，请重新选择更新版本。';
        }

        //获取远程列表
        $remote_data = UpgradeLogic::getRemoteVersion()['lists'] ?? [];
        if (empty($remote_data)) {
            return '获取更新数据失败';
        }

        foreach ($remote_data as $k => $item) {
            if ($item['version_no'] != $local_version) {
                continue;
            }

            if (empty($remote_data[$k - 1])) {
                return '已为最新版本';
            }

            if ($remote_data[$k - 1]['version_no'] != $target_version) {
                return '当前系统无法升级到该版本，请重新选择更新版本。';
            }
        }
        return true;
    }

}