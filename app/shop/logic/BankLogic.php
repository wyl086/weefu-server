<?php


namespace app\shop\logic;


use app\common\basics\Logic;
use app\common\model\shop\ShopBank;

class BankLogic extends Logic
{
    /**
     * @Notes: 银行列表
     * @Author: 张无忌
     * @param $get
     * @param $shop_id
     * @return array
     */
    public static function lists($get, $shop_id)
    {
        try {
            $model = new ShopBank();
            $lists = $model->field(true)
                ->where(['del' => 0, 'shop_id'=>$shop_id])
                ->order('id', 'desc')
                ->paginate([
                    'page' => $get['page'] ?? 1,
                    'list_rows' => $get['limit'] ?? 20,
                    'var_page' => 'page'
                ])->toArray();

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 获取商家银行卡账号列表
     * @Author: 张无忌
     * @param $shop_id
     * @return array
     */
    public static function getBankByShopId($shop_id)
    {
        try {
            $model = new ShopBank();
            return $model->field(true)
                ->where(['del' => 0, 'shop_id'=>$shop_id])
                ->order('id', 'desc')
                ->select()->toArray();
        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 银行卡详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new ShopBank();
        return $model->field(true)->findOrEmpty($id);
    }

    /**
     * @Notes: 新增银行卡账号
     * @Author: 张无忌
     * @param $post
     * @param $shop_id
     * @return bool
     */
    public static function add($post, $shop_id)
    {
        try {
            ShopBank::create([
                'shop_id'  => $shop_id,
                'name'     => $post['name'],
                'branch'   => $post['branch'],
                'nickname' => $post['nickname'],
                'account'  => $post['account'],
                'del'      => 0
            ]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 编辑银行卡
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function edit($post)
    {
        try {
            ShopBank::update([
                'name'     => $post['name'],
                'branch'   => $post['branch'],
                'nickname' => $post['nickname'],
                'account'  => $post['account'],
                'del'      => 0
            ], ['id'=>$post['id']]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 删除银行卡
     * @Author: 张无忌
     * @param $id
     * @return bool
     */
    public static function del($id)
    {
        try {
            ShopBank::update([
                'del'         => 1,
                'update_time' => time()
            ], ['id'=>$id]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
}