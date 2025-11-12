<?php

namespace app\api\logic;

use app\api\validate\PlaceOrderInvoiceValidate;
use app\common\basics\Logic;
use app\common\enum\OrderInvoiceEnum;
use app\common\enum\ShopEnum;
use app\common\model\order\Order;
use app\common\model\order\OrderInvoice;
use app\common\model\shop\Shop;

/**
 * 订单发票逻辑
 * Class OrderInvoiceLogic
 * @package app\api\logic
 */
class OrderInvoiceLogic extends Logic
{

    /**
     * @notes 添加发票
     * @param $params
     * @return bool
     * @author 段誉
     * @date 2022/4/12 10:11
     */
    public static function add($params): bool
    {
        try {
            $order = Order::with(['shop'])->findOrEmpty($params['order_id']);

            OrderInvoice::create([
                'shop_id' => $order['shop']['id'],
                'user_id' => $order['user_id'],
                'order_id' => $order['id'],
                'type' => $params['type'],
                'header_type' => $params['header_type'],
                'name' => $params['name'],
                'duty_number' => $params['duty_number'] ?? '',
                'email' => $params['email'],
                'mobile' => $params['mobile'] ?? '',
                'address' => $params['address'] ?? '',
                'bank' => $params['bank'] ?? '',
                'bank_account' => $params['bank_account'] ?? '',
                'invoice_amount' => $order['order_amount'],
                'create_time' => time()
            ]);

            return true;

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 编辑发票
     * @param $params
     * @return bool
     * @author 段誉
     * @date 2022/4/12 10:30
     */
    public static function edit($params) : bool
    {
        try {
            OrderInvoice::update([
                'type' => $params['type'],
                'header_type' => $params['header_type'],
                'name' => $params['name'],
                'duty_number' => $params['duty_number'] ?? '',
                'email' => $params['email'],
                'mobile' => $params['mobile'] ?? '',
                'address' => $params['address'] ?? '',
                'bank' => $params['bank'] ?? '',
                'bank_account' => $params['bank_account'] ?? '',
                'create_time' => time()
            ], ['id' => $params['id']]);

            return true;

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 发票详情
     * @param $params
     * @return array|\think\Model
     * @author 段誉
     * @date 2022/4/12 12:12
     */
    public static function detail($params)
    {
        return OrderInvoice::findOrEmpty($params['id'])->toArray();
    }


    /**
     * @notes 通过订单id获取发票信息
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/4/12 9:24
     */
    public static function getInvoiceDetailByOrderId($id): array
    {
        $result = Order::field(['id', 'order_sn', 'shop_id', 'order_amount', 'order_status', 'create_time'])
            ->with([
                'shop',
                'order_goods',
                'invoice' => function ($query) {
                    $query->withoutField(['invoice_time', 'update_time']);
                    $query->append(['status_text', 'type_text', 'header_type_text']);
                }
            ])
            ->append(['order_status_text'])
            ->findOrEmpty($id)->toArray();

        return $result;
    }


    /**
     * @notes 校验订单发票
     * @param $params
     * @return array|false
     * @author 段誉
     * @date 2022/4/11 15:34
     */
    public static function checkOrderInvoice($params, $type = null)
    {
        if (empty($params['invoice'])) {
            return [];
        }

        try {
            if($type == 'team') {
                $invoiceParams = $params['invoice'];
            } else {
                $invoiceParams = json_decode($params['invoice'], true);
            }

            $invoiceParams = array_column($invoiceParams, null, 'shop_id');

            $shops = Shop::whereIn('id', array_keys($invoiceParams))->column('*', 'id');

            foreach ($invoiceParams as $shopId => $item) {
                if (!isset($shops[$shopId])) {
                    continue;
                }

                $shop = $shops[$shopId];

                // 商家不支持开发票
                if ($shop['open_invoice'] == ShopEnum::INVOICE_CLOSE) {
                    throw new \Exception($shop['name'] . '店铺不支持开具发票');
                }

                // 选择的发票类型为专票但是该店铺不支持专票
                if ($item['type'] == OrderInvoiceEnum::TYPE_SPEC && $shop['spec_invoice'] == ShopEnum::SPEC_INVOICE_UNABLE) {
                    throw new \Exception($shop['name'] . '不支持开具专票');
                }

                // 校验参数
                validate(PlaceOrderInvoiceValidate::class)->check($item);
            }

            return $invoiceParams;

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }



    /**
     * @notes 下单时添加发票
     * @param $shopId
     * @param $userId
     * @param $orderId
     * @param $invoice // 订单中提交的发票信息，以门店id为键
     * @return OrderInvoice|\think\Model|void
     * @author 段誉
     * @date 2022/4/11 17:46
     */
    public static function insertOrderInvoice($shopId, $userId, $orderId, $invoice)
    {
        $order = Order::findOrEmpty($orderId);

        foreach ($invoice as $key => $item) {
            // 此处$key 为 店铺id
            if ($shopId != $key) {
                continue;
            }
            return OrderInvoice::create([
                'shop_id' => $shopId,
                'user_id' => $userId,
                'order_id' => $orderId,
                'type' => $item['type'],
                'header_type' => $item['header_type'],
                'name' => $item['name'],
                'duty_number' => $item['duty_number'] ?? '',
                'email' => $item['email'],
                'mobile' => $item['mobile'] ?? '',
                'address' => $item['address'] ?? '',
                'bank' => $item['bank'] ?? '',
                'bank_account' => $item['bank_account'] ?? '',
                'invoice_amount' => $order['order_amount'],
                'create_time' => time()
            ]);
        }
    }


    /**
     * @notes 获取商家发票设置
     * @param $params
     * @return array
     * @author 段誉
     * @date 2022/4/12 15:32
     */
    public static function getInvoiceSetting($params)
    {
        return Shop::field('id,open_invoice,spec_invoice')->findOrEmpty($params['shop_id'])->toArray();
    }


}
