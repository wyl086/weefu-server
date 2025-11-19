<?php


namespace app\shop\logic;


use app\common\basics\Logic;
use app\common\enum\ShopEnum;
use app\common\model\shop\Shop;
use app\common\server\UrlServer;

class StoreLogic extends Logic
{
    /**
     * @Notes: 获取商家详细
     * @Author: 张无忌
     * @param $shop_id
     * @return array
     */
    public static function detail($shop_id)
    {
        $model = new Shop();
        $detail = $model->field(true)
            ->with(['category'])
            ->json(['other_qualifications'],true)
            ->findOrEmpty($shop_id)->toArray();

        $detail['category'] = $detail['category']['name'] ?? '未知';
        $detail['type'] = ShopEnum::getShopTypeDesc($detail['type']);
        $detail['run_start_time'] = $detail['run_start_time'] ? date('H:i:s', $detail['run_start_time']) : '';
        $detail['run_end_time'] = $detail['run_end_time'] ? date('H:i:s', $detail['run_end_time']) : '';
        $detail['business_license'] = $detail['business_license'] ? UrlServer::getFileUrl($detail['business_license']) : '';
        if (!empty($detail['other_qualifications'])) {
            foreach ($detail['other_qualifications'] as &$val) {
                $val = UrlServer::getFileUrl($val);
            }
        }
        return $detail;
    }

    /**
     * @Notes: 修改商家信息
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function edit($post)
    {
        try {
            $num = count($post['other_qualifications'] ?? []);
            if ($num > 5) {
                throw new \Exception('其他资质图片不能超过五张', 10006);
            }

            // 校验配送方式
            self::checkDeliveryType($post);

            Shop::update([
                'nickname'       => $post['nickname'],
                'mobile'         => $post['mobile'],
                'keywords'       => $post['keywords'] ?? '',
                'intro'          => $post['intro'] ?? '',
                'is_run'         => $post['is_run'],
//                'service_mobile' => $post['service_mobile'],
                'weekdays'       => $post['weekdays'] ?? '',
                'province_id'    => $post['province_id'] ?? 0,
                'city_id'        => $post['city_id'] ?? 0,
                'district_id'    => $post['district_id'] ?? 0,
                'address'        => $post['address'] ?? '',
                'longitude'      => $post['longitude'] ?? '',
                'latitude'       => $post['latitude'] ?? '',
                'run_start_time' => empty($post['run_start_time']) ? '' : strtotime($post['run_start_time']),
                'run_end_time'   => empty($post['run_end_time']) ? '' : strtotime($post['run_end_time']),
                'refund_address' => json_encode([
                    'nickname'    => $post['refund_nickname'],
                    'mobile'      => $post['refund_mobile'],
                    'province_id' => $post['refund_province_id'],
                    'city_id'     => $post['refund_city_id'],
                    'district_id' => $post['refund_district_id'],
                    'address'     => $post['refund_address'],
                ], JSON_UNESCAPED_UNICODE),
                'business_license'   => empty($post['business_license']) ? '' : UrlServer::setFileUrl($post['business_license']),
                'other_qualifications' => isset($post['other_qualifications']) ? json_encode($post['other_qualifications'], JSON_UNESCAPED_UNICODE) : '',
                'open_invoice'  => $post['open_invoice'] ?? 0,
                'spec_invoice'  => $post['spec_invoice'] ?? 0,
                'delivery_type' => $post['delivery_type'],
                'discount'      => $post['discount'] ?? 0.00
            ], ['id'=>$post['id']]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
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