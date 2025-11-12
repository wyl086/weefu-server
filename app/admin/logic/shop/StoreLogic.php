<?php


namespace app\admin\logic\shop;


use app\common\basics\Logic;
use app\common\enum\ShopEnum;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopAdmin;
use app\common\server\UrlServer;
use Exception;
use think\facade\Db;

class StoreLogic extends Logic
{
    /**
     * NOTE: 商家列表
     * @author: 张无忌
     * @param $get
     * @return array|bool
     */
    public static function lists($get)
    {
        try {
            $where = [
                ['del', '=', 0]
            ];

            if (!empty($get['name']) and $get['name'])
                $where[] = ['name', 'like', '%'.$get['name'].'%'];

            if (!empty($get['type']) and is_numeric($get['type']))
                $where[] = ['type', '=', $get['type']];

            if (!empty($get['cid']) and is_numeric($get['cid']))
                $where[] = ['cid', '=', $get['cid']];

            if (isset($get['is_recommend']) && $get['is_recommend'] != '')
                $where[] = ['is_recommend', '=', $get['is_recommend']];

            if (isset($get['is_run']) && $get['is_run'] != '')
                $where[] = ['is_run', '=', $get['is_run']];

            if (isset($get['is_freeze']) and $get['is_freeze'] != '')
                $where[] = ['is_freeze', '=', $get['is_freeze']];

            if (!empty($get['expire_start_time']) and $get['expire_start_time'])
                $where[] = ['expire_time', '>=', strtotime($get['expire_start_time'])];

            if (!empty($get['expire_end_time']) and $get['expire_end_time'])
                $where[] = ['expire_time', '<=', strtotime($get['expire_end_time'])];

            $condition = 'del=0';
            // 到期状态
            if (isset($get['expire_status']) and $get['expire_status'] != '') {
                if ($get['expire_status']) {
                    // 已到期
                    $where[] = ['expire_time', '<', time()];
                    $where[] = ['expire_time', '>', 0];
                } else {
                    // 未到期
                    $condition = "expire_time=0 OR expire_time >". time();
                }
            }

            $model = new Shop();
            $lists = $model->field(true)
                ->where($where)
                ->whereRaw($condition)
                ->order('id', 'desc')
                ->order('weight', 'asc')
                ->with(['category', 'admin'])
                ->append(['expire_desc'])
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])
                ->toArray();

            foreach ($lists['data'] as &$item) {
                $item['category']  = $item['category']['name'] ?? '未知';
                $item['type']      = ShopEnum::getShopTypeDesc($item['type']);
                $item['is_run']    = ShopEnum::getShopIsRunDesc($item['is_run']);
                $item['is_freeze'] = ShopEnum::getShopFreezeDesc($item['is_freeze']);
                $item['is_recommend'] = ShopEnum::getShopIsRecommendDesc($item['is_recommend']);
                $item['account'] = $item['admin']['account'] ?? '';
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * NOTE: 商家详细
     * @author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new Shop();
        $detail = $model->json(['other_qualifications'],true)->findOrEmpty($id)->toArray();
        $detail['expire_time'] = $detail['expire_time'] == '无期限' ? 0 : $detail['expire_time'];

        $detail['business_license'] = $detail['business_license'] ? UrlServer::getFileUrl($detail['business_license']) : '';
        if (!empty($detail['other_qualifications'])) {
            foreach ($detail['other_qualifications'] as &$val) {
                $val = UrlServer::getFileUrl($val);
            }
        }

        return $detail;
    }

    public static function getAccountInfo($id)
    {
        $detail = ShopAdmin::field('id,account')->where(['shop_id' => $id, 'root' => 1])->findOrEmpty()->toArray();
        return $detail;
    }

    /**
     * NOTE: 新增商家
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function add($post)
    {
        Db::startTrans();
        try {
            // 校验配送方式
            self::checkDeliveryType($post);

            // 创建商家
            $shop = Shop::create([
                'cid'               => $post['cid'],
                'type'              => $post['type'],
                'name'              => $post['name'],
                'nickname'          => $post['nickname'],
                'mobile'            => $post['mobile'],
                'logo'              => $post['logo'] ?? '',
                'background'        => $post['background'] ?? '',
                'license'           => $post['license'] ?? '',
                'keywords'          => $post['keywords'] ?? '',
                'intro'             => $post['intro'] ?? '',
                'weight'            => $post['weight'] ?? 0,
                'trade_service_fee' => $post['trade_service_fee'],
                'is_run'            => $post['is_run'],
                'is_freeze'         => $post['is_freeze'],
                'is_product_audit'  => $post['is_product_audit'],
                'is_recommend'      => $post['is_recommend'] ?? 0,
                'expire_time'       => !empty($post['expire_time']) ? strtotime($post['expire_time']) : 0,
                'province_id'    => $post['province_id'] ?? 0,
                'city_id'        => $post['city_id'] ?? 0,
                'district_id'    => $post['district_id'] ?? 0,
                'address'        => $post['address'] ?? '',
                'longitude'      => $post['longitude'] ?? '',
                'latitude'       => $post['latitude'] ?? '',
                'delivery_type'  => $post['delivery_type'] ?? [1]
            ]);
            // 创建账号
            // 新增商家登录账号
            $time = time();
            $salt = substr(md5($time . $post['name']), 0, 4);//随机4位密码盐
            ShopAdmin::create([
                'root' => 1,
                'shop_id' => $shop->id,
                'name' => '超级管理员',
                'account' => $post['account'],
                'password' => generatePassword($post['password'], $salt),
                'salt' => $salt,
                'role_id' => 0,
                'create_time' => $time,
                'update_time' => $time,
                'disable' => 0,
                'del' => 0
            ]);

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 编辑商家
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function edit($post)
    {
        try {
            // 校验配送方式
            self::checkDeliveryType($post);

            Shop::update([
                'cid'               => $post['cid'],
                'type'              => $post['type'],
                'name'              => $post['name'],
                'nickname'          => $post['nickname'],
                'mobile'            => $post['mobile'],
                'logo'              => $post['logo'] ?? '',
                'keywords'          => $post['keywords'] ?? '',
                'intro'             => $post['intro'] ?? '',
                'trade_service_fee' => $post['trade_service_fee'],
                'is_run'            => $post['is_run'],
                'is_freeze'         => $post['is_freeze'],
                'is_product_audit'  => $post['is_product_audit'],
                'expire_time'       => !empty($post['expire_time']) ? strtotime($post['expire_time']) : 0,
                'province_id'    => $post['province_id'] ?? 0,
                'city_id'        => $post['city_id'] ?? 0,
                'district_id'    => $post['district_id'] ?? 0,
                'address'        => $post['address'] ?? '',
                'longitude'      => $post['longitude'] ?? '',
                'latitude'       => $post['latitude'] ?? '',
                'delivery_type'  => $post['delivery_type'] ?? [1],
                'business_license'   => empty($post['business_license']) ? '' : UrlServer::setFileUrl($post['business_license']),
                'other_qualifications' => isset($post['other_qualifications']) ? json_encode($post['other_qualifications'], JSON_UNESCAPED_UNICODE) : '',
            ], ['id'=>$post['id']]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 设置商家
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function set($post)
    {
        try {
            Shop::update([
                'is_distribution' => $post['is_distribution'] ?? 0,
                'is_recommend' => $post['is_recommend'] ?? 0,
                'is_pay' => $post['is_pay'] ?? 1, //是否开启支付功能,默认开启
                'weight'       => $post['weight']
            ], ['id'=>$post['id']]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 更新账号密码
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function account($post)
    {
        Db::startTrans();
        try {
            if(!isset($post['account']) || empty($post['account'])) {
                throw new \think\Exception('账户不能为空');
            }
            $shopAdmin = ShopAdmin::where([
                ['account', '=', trim($post['account'])],
                ['shop_id', '<>', $post['id']]
            ])->findOrEmpty();
            if(!$shopAdmin->isEmpty()) {
                throw new \think\Exception('账户已存在，请更换其他名称重试');
            }

            $shopAdmin = ShopAdmin::where(['shop_id' => $post['id'], 'root' => 1])->findOrEmpty();

            $shopAdminUpdateData = [
                'account'     => $post['account'],
                'update_time' => time()
            ];
            if (!empty($post['password'])) {
                $shopAdminUpdateData['password'] = generatePassword($post['password'], $shopAdmin->salt);
            }
            ShopAdmin::where(['shop_id' => $post['id'], 'root' => 1])->update($shopAdminUpdateData);

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            static::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 批量更新商家营业状态或冻结状态
     * @param $ids
     * @param $field
     * @param $value
     * @return Shop|false
     * @author 段誉
     * @date 2022/3/17 10:42
     */
    public static function batchOperation($ids, $field, $value)
    {
        try {
            $result = Shop::whereIn('id', $ids)->update([
                $field  => $value,
                'update_time' => time()
            ]);
            return $result;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 校验配送方式
     * @param $post
     * @return bool
     * @throws \Exception
     * @author 段誉
     * @date 2022/11/1 11:30
     */
    public static function checkDeliveryType($post)
    {
        // 校验配送方式
        if (empty($post['delivery_type'])) {
            throw new \Exception('至少选择一种配送方式');
        }

        // 线下自提时，商家地址必填
        if (in_array(ShopEnum::DELIVERY_SELF, $post['delivery_type'])) {
            if (empty($post['province_id']) || empty($post['city_id']) || empty($post['district_id']) || empty($post['address'])) {
                throw new \Exception('线下自提需完善商家地址');
            }
        }
        return true;
    }
}
