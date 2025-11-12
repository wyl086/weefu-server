<?php
namespace app\api\logic;

use app\common\basics\Logic;
use app\common\model\shop\ShopFollow;
use app\common\model\shop\ShopCategory;
use app\common\server\UrlServer;

class ShopFollowLogic extends Logic
{
    /**
     * 店铺： 关注/取消关注
     */
    public static function changeStatus($shopId, $userId)
    {
        $data = ShopFollow::where(['shop_id'=>$shopId,'user_id'=>$userId])->findOrEmpty();
        if($data->isEmpty()) { // 没数据，首次关注
            $insertData = [
                'shop_id' => $shopId,
                'user_id' => $userId,
                'status' => 1,
                'create_time' => time()
            ];
            $result = ShopFollow::create($insertData);
            return [
                'result' => $result,
                'msg' => '关注成功'
            ];
        }else{ // 关注过，修改关注状态
            $newStatus = $data['status'] ? 0 : 1;
            $msg = $newStatus ? '关注成功' : '取消关注';
            $updateData = [
                'id' => $data['id'],
                'status' => $newStatus,
                'update_time' => time()
            ];
            $result = ShopFollow::update($updateData);
            return [
                'result' => $result,
                'msg' => $msg
            ];
        }
    }


    public static function lists($get)
    {
        $where = [
            'sf.user_id' => $get['user_id'],
            'sf.status' => 1
        ];

        $lists = ShopFollow::alias('sf')
            ->field('s.id,s.name,s.cid,s.type,s.logo,s.score')
            ->leftJoin('shop s', 's.id=sf.shop_id')
            ->where($where)
            ->order('sf.update_time', 'desc')
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();

        $count = ShopFollow::alias('sf')->where($where)->count();

        $typeDesc = [1=>'官方自营', 2=>'入驻商家'];
        foreach($lists as &$item) {
            // 商家类型
            $item['type_desc'] = $typeDesc[$item['type']];
            // 主营类目
            $item['cid_desc'] = ShopCategory::where('id', $item['cid'])->value('name');
            // logo
            $item['logo'] = UrlServer::getFileUrl($item['logo']);
        }

        $data = [
            'lists' => $lists,
            'count' => $count,
            'more' => is_more($count, $get['page_no'], $get['page_size']),
            'page_no' => $get['page_no'],
            'page_size' => $get['page_size'],
        ];
        return $data;
    }
}