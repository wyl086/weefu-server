<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\enum\AdEnum;
use app\common\model\Ad;
use think\facade\Db;
use app\common\server\UrlServer;

class AdLogic extends Logic
{
    public static function lists($pid,$terminal)
    {
        $ad_list = Ad::alias('a')
            ->join('ad_position ap', 'a.pid = ap.id')
            ->where(['pid' => $pid, 'ap.terminal' => $terminal, 'a.status' => 1, 'a.del' => 0, 'ap.status' => 1, 'ap.del' => 0])
            ->field('a.*')
            ->order('a.sort asc, a.id desc')
            ->select()
            ->toArray();

        $list = [];
        foreach ($ad_list as $key => $ad) {
            $url = $ad['link'];
            $is_tab = 0;
            $params = [];
            switch ($ad['link_type']) {
                case 1: // 商城页面
                    $page = AdEnum::getLinkPage($ad['terminal'], $ad['link']);
                    $url = $page['path'];
                    $is_tab = $page['is_tab'] ?? 0;
                    break;
                case 2: // 商品页面
                    $goods_path = AdEnum::getGoodsPath($ad['terminal']);
                    $url = $goods_path;
                    $params = [
                        'id' => $ad['link'],
                    ];
                    break;
            }
            $list[] = [
                'image'     => UrlServer::getFileUrl($ad['image']),
                'link'      => $url,
                'link_type' => $ad['link_type'],
                'params'    => $params,
                'is_tab'    => $is_tab,
            ];
        }
        return $list;
    }
}