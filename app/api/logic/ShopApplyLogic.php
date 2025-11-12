<?php


namespace app\api\logic;


use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\model\shop\ShopApply;
use app\common\model\shop\ShopCategory;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\Exception;

class ShopApplyLogic extends Logic
{
    /**
     * @Notes: 商家申请入驻
     * @Author: 张无忌
     * @param $post
     * @param $user_id
     * @return bool|array
     */
    public static function apply($post, $user_id)
    {
        try {
            // 验证商家名称及账号是否已存在
            $applyInfo = ShopApply::where([
                ['del', '=', 0],
                ['audit_status', '<>', 3],
                ['name', '=', $post['name']],
            ])->select()->toArray();
            if($applyInfo) {
                throw new Exception('商家名称已存在');
            }
            $applyInfo = ShopApply::where([
                ['del', '=', 0],
                ['audit_status', '<>', 3],
                ['account', '=', $post['account']],
            ])->select()->toArray();
            if($applyInfo) {
                throw new Exception('商家账号已存在');
            }
            $apply = ShopApply::create([
                'user_id'       => $user_id,
                'cid'           => $post['cid'],
                'name'          => $post['name'],
                'nickname'      => $post['nickname'],
                'mobile'        => $post['mobile'],
                'account'       => $post['account'],
                'password'      => $post['password'],
                'license'       => implode(',', $post['license']),
                'del'           => 0,
                'audit_status'  => 1,
                'audit_explain' => '',
                'apply_time'    => time()
            ]);

            $platform_contacts = ConfigServer::get('website_platform','platform_mobile');
            if (!empty($platform_contacts)) {
                //通知平台
                event('Notice', [
                    'scene' => NoticeEnum::SHOP_APPLY_NOTICE_PLATFORM,
                    'mobile' => $platform_contacts,
                    'params' => [
                        'user_id' => $user_id,
                        'shop_name' => $post['name'],
                    ]
                ]);
            }


            return ['id'=>$apply->id];
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 申请记录列表
     * @Author: 张无忌
     * @param $get
     * @param $user_id
     * @return array
     */
    public static function  record($get, $user_id)
    {
        try {
            $model = new ShopApply();
            $lists = $model->field('id,name,apply_time,audit_status,audit_status as audit_status_desc')
                ->order('id', 'desc')
                ->where([
                    ['user_id', '=', $user_id],
                    ['del', '=', 0]
                ])
                ->page($get['page_no'], $get['page_size'])
                ->select()
                ->toArray();

            $count = $model->field('id,name,apply_time,audit_status as audit_status_desc')
                ->where([
                    ['user_id', '=', $user_id],
                    ['del', '=', 0]
                ])
                ->count();

            return [
                'count' => $count,
                'lists' => $lists,
                'page_no' => $get['page_no'],
                'page_size' => $get['page_size'],
                'more' => is_more($count, $get['page_no'], $get['page_size'])
            ];

        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 申请详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new ShopApply();
        $info = $model->field(true)->findOrEmpty($id)->toArray();
        if(!empty($info['license'])) {
            foreach($info['license'] as $key => $item) {
                $info['license'][$key] = UrlServer::getFileUrl($item);
            }
        }
        $shop_category = ShopCategory::where('del', 0)->column('id,name', 'id');
        $info['admin_address'] = request()->domain().'/shop';
        $info['cid_desc'] = $shop_category[$info['cid']]['name'];
        return $info;
    }
}