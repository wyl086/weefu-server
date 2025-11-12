<?php


namespace app\admin\logic\shop;


use app\common\basics\Logic;
use app\common\enum\NoticeEnum;
use app\common\enum\ShopEnum;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopAdmin;
use app\common\model\shop\ShopApply;
use app\common\server\UrlServer;
use Exception;
use think\facade\Db;

class ApplyLogic extends Logic
{
    /**
     * NOTE: 获取申请列表
     * @param array $get
     * @return array
     * @author 张无忌
     */
    public static function lists($get)
    {
        try {

            $type = [
                ['audit_status', '=', ShopEnum::AUDIT_STATUS_STAY],
                ['audit_status', '=', ShopEnum::AUDIT_STATUS_OK],
                ['audit_status', '=', ShopEnum::AUDIT_STATUS_REFUSE]
            ];
            $get['type'] = $get['type'] ?? 1;
            $where[] = $type[intval($get['type']) - 1];

            if (!empty($get['name']) and $get['name'])
                $where[] = ['name', 'like', '%'.$get['name'].'%'];

            if (!empty($get['nickname']) and $get['nickname'])
                $where[] = ['nickname', 'like', '%'.$get['nickname'].'%'];

            if (!empty($get['apply_start_time']) and $get['apply_start_time'])
                $where[] = ['apply_time', '>=', strtotime($get['apply_start_time'])];

            if (!empty($get['apply_end_time']) and $get['apply_end_time'])
                $where[] = ['apply_time', '<=', strtotime($get['apply_end_time'])];


            $model = new ShopApply();
            $lists = $model->field(true)
                ->where($where)
                ->where(['del'=>0])
                ->with(['category'])
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])
                ->toArray();


            foreach ($lists['data'] as &$item) {
                $item['category']     = $item['category']['name'] ?? '未知类目';
                $item['audit_status_desc'] = ShopEnum::getAuditStatusDesc($item['audit_status']);

                $license = [];
                foreach ($item['license'] as $url) {
                    $license[] = UrlServer::getFileUrl($url);
                }

                $item['license'] = $license;
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * NOTE: 统计
     * @author: 张无忌
     * @return array
     */
    public static function totalCount()
    {
        $type = [
            ['audit_status', '=', ShopEnum::AUDIT_STATUS_STAY],
            ['audit_status', '=', ShopEnum::AUDIT_STATUS_OK],
            ['audit_status', '=', ShopEnum::AUDIT_STATUS_REFUSE]
        ];

        $model = new ShopApply();
        $ok     = $model->where(['del'=>0])->where([$type[ShopEnum::AUDIT_STATUS_OK - 1]])->count();
        $stay   = $model->where(['del'=>0])->where([$type[ShopEnum::AUDIT_STATUS_STAY - 1]])->count();
        $refuse = $model->where(['del'=>0])->where([$type[ShopEnum::AUDIT_STATUS_REFUSE - 1]])->count();

        return [
            'ok'     => $ok,
            'stay'   => $stay,
            'refuse' => $refuse
        ];

    }

    /**
     * NOTE: 详细
     * @param $id
     * @return array
     * @author: 张无忌
     */
    public static function detail($id)
    {
        $model = new ShopApply();
        $detail = $model->field(true)
            ->where(['id'=>(int)$id])
            ->with(['category'])
            ->findOrEmpty()->toArray();

        $detail['category']      = $detail['category']['name'] ?? '未知类目';
        $detail['audit_status']  = ShopEnum::getAuditStatusDesc($detail['audit_status']);
        $detail['audit_explain'] = $detail['audit_explain'] == '' ? '无' : $detail['audit_explain'];
        return $detail;
    }

    /**
     * NOTE: 审核
     * @param $post
     * @return bool
     * @author: 张无忌
     */
    public static function audit($post)
    {
        Db::startTrans();
        try {
            ShopApply::update([
                'audit_status'  => $post['audit_status'],
                'audit_explain' => $post['audit_explain'] ?? ''
            ], ['id'=>(int)$post['id']]);

            $model = new ShopApply();
            $shopApply = $model->field(true)->findOrEmpty((int)$post['id'])->toArray();

            if ($post['audit_status'] == ShopEnum::AUDIT_STATUS_OK) {
                // 新增商家信息
                $shop = Shop::create([
                    'cid'      => $shopApply['cid'],
                    'type'     => ShopEnum::SHOP_TYPE_IN,
                    'name'     => $shopApply['name'],
                    'nickname' => $shopApply['nickname'],
                    'mobile'   => $shopApply['mobile'],
                    'license'  => $shopApply['license'],
                    'logo'              => '',
                    'background'        => '',
                    'keywords'          => '',
                    'intro'             => '',
                    'weight'            => 0,
                    'trade_service_fee' => 0,
                    'is_run'            => ShopEnum::SHOP_RUN_CLOSE,
                    'is_freeze'         => ShopEnum::SHOP_FREEZE_NORMAL,
                    'is_product_audit'  => ShopEnum::PRODUCT_AUDIT_TRUE,
                    'is_recommend'      => ShopEnum::SHOP_RECOMMEND_FALSE,
                    'del'               => 0,
                    'expire_time'       => 0,
                ]);

                // 新增商家登录账号
                $time = time();
                $salt = substr(md5($time . $shopApply['name']), 0, 4);//随机4位密码盐
                ShopAdmin::create([
                    'shop_id' => $shop->id,
                    'name' => '超级管理员',
                    'account' => $shopApply['account'],
                    'password' => generatePassword($shopApply['password'], $salt),
                    'salt' => $salt,
                    'role_id' => 0,
                    'create_time' => $time,
                    'update_time' => $time,
                    'disable' => 0,
                    'del' => 0
                ]);

                //成功通知
                event('Notice', [
                    'scene' => NoticeEnum::SHOP_APPLY_SUCCESS_NOTICE,
                    'mobile' => $shopApply['mobile'],
                    'params' => [
                        'user_id'           => $shopApply['user_id'],
                        'shop_name'         => $shopApply['name'],
                        'shop_admin_url'    => request()->domain().'/shop',
                        'shop_admin_account' => $shopApply['account'],
                    ]
                ]);
            } else {
                //失败通知
                event('Notice', [
                    'scene' => NoticeEnum::SHOP_APPLY_ERROR_NOTICE,
                    'mobile' => $shopApply['mobile'],
                    'params' => [
                        'user_id'           => $shopApply['user_id'],
                        'shop_name'         => $shopApply['name'],
                    ]
                ]);
            }
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 删除
     * @author: 张无忌
     * @param $id
     * @return bool
     */
    public static function del($id)
    {
        try {
            ShopApply::update([
                'del' => 1,
                'update_time' => time()
            ], ['id'=>(int)$id]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
}