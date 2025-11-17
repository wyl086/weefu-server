<?php
namespace app\api\logic;

use app\admin\logic\distribution\DistributionSettingLogic;
use app\common\basics\Logic;
use app\common\model\distribution\Distribution;
use app\common\model\MenuDecorate;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use app\common\enum\MenuEnum;

class MenuLogic extends Logic
{
    public static function getMenu($type, $userId = null)
    {
        $list = MenuDecorate::where(['decorate_type' => $type, 'del' => 0, 'is_show' => 1])
            ->field('name,image,link_type,link_address,appid')
            ->order('sort desc')
            ->select()
            ->toArray();

        $menu_list = [];

        switch($type) {
            case 1:
                $type_desc = 'index';
                break;
            case 2:
                $type_desc = 'center';
                break;
        }

        // 获取分销配置
        $config = DistributionSettingLogic::getConfig();
        $distribution = Distribution::where('user_id', $userId)->findOrEmpty()->toArray();
        $isDistribution = 0;
        if (!empty($distribution) && $distribution['is_distribution'] == 1) {
            $isDistribution = 1;
        }
        foreach ($list as $key => $menu) {
            $menu_content = [];
            if(1 == $menu['link_type']){
                $menu_content = MenuEnum::getMenu($type_desc, $menu['link_address']);

            }
            // 分销功能关闭，跳过分销推广菜单
            if (!$config['is_open'] && $type == 2 && 201 == $menu['link_address']) {
                continue;
            }
            // 开通分销会员为指定分销 且 当前用户为非分销会员，跳转分销推广菜单
            if ($config['apply_condition'] == 3 && !$isDistribution && $type == 2 && 201 == $menu['link_address']) {
                continue;
            }

            //处理图标
            $menu_list[] = [
                'name' => $menu['name'],
                'image' => UrlServer::getFileUrl($menu['image']),
                'link' => $menu_content['link'] ?? $menu['link_address'],
                'is_tab' => $menu_content['is_tab'] ?? '',
                'link_type' => $menu_content['link_type'] ?? $menu['link_type'],
                'appid' => $menu['appid'] ?? '',
            ];
        }
        return $menu_list;
    }
}
