<?php

namespace app\api\validate;


use app\common\basics\Validate;
use app\common\enum\GoodsEnum;
use app\common\enum\ShopEnum;
use app\common\model\community\CommunityArticle;
use app\common\model\goods\Goods;
use app\common\model\shop\Shop;

/**
 * 种草社区文章验证
 * Class CommunityArticleValidate
 * @package app\api\validate
 */
class CommunityArticleValidate extends Validate
{

    protected $rule = [
        'id' => 'require|checkArticle',
        'content' => 'require|min:10|max:999',
        'image' => 'require|checkImage',
        'goods' => 'checkGoods',
        'shop' => 'checkShop',
    ];

    protected $message = [
        'id.require' => '参数缺失',
        'content.require' => '写够10个字，才可以发布和被精选哦',
        'content.min' => '至少输入10个字符',
        'content.max' => '至多输入999个字符',
        'image.require' => '至少要添加1张图片哦',
    ];


    /**
     * @notes 添加场景
     * @return CommunityArticleValidate
     * @author 段誉
     * @date 2022/5/7 9:46
     */
    public function sceneAdd()
    {
        return $this->remove('id', true);
    }


    /**
     * @notes 编辑场景
     * @author 段誉
     * @date 2022/5/7 9:47
     */
    public function sceneEdit()
    {
    }


    /**
     * @notes 删除场景
     * @author 段誉
     * @date 2022/5/7 9:47
     */
    public function sceneDel()
    {
        return $this->only(['id']);
    }

    /**
     * @notes 校验文章
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/5/7 9:50
     */
    protected function checkArticle($value, $rule, $data)
    {
        $article = CommunityArticle::findOrEmpty($value);

        if ($article->isEmpty()) {
            return '信息不存在';
        }

        if ($article['del'] == 1) {
            return '已被删除';
        }

        return true;
    }


    /**
     * @notes 校验图片数量
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/29 10:53
     */
    protected function checkImage($value, $rule, $data)
    {
        if (count($value) > 9) {
            return '最多上传9张图片';
        }
        return true;
    }


    /**
     * @notes 校验所选商品
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/29 10:53
     */
    protected function checkGoods($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        if (!empty($data['shop'])) {
            return '不能同时选择宝贝/店铺';
        }

        if (count($value) > 5) {
            return '最多只能选择5个商品';
        }

        $goods_id = array_unique($value);
        $where = [
            ['del', '=', GoodsEnum::DEL_NORMAL],  // 未删除
            ['status', '=', GoodsEnum::STATUS_SHELVES], // 上架中
            ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK], // 审核通过
            ['id', 'in', $goods_id]
        ];
        $goods = Goods::where($where)->column('*', 'id');

        foreach ($value as $item) {
            if (!isset($goods[$item])) {
                return '所选商品中包含已下架商品';
            }
        }

        return true;
    }


    /**
     * @notes 校验所选店铺
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author 段誉
     * @date 2022/4/29 10:54
     */
    protected function checkShop($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        if (!empty($data['goods'])) {
            return '不能同时选择宝贝/店铺';
        }

        if (count($value) > 3) {
            return '最多只能选择3个店铺';
        }

        $shop_id = array_unique($value);
        $where = [
            ['is_freeze', '=', ShopEnum::SHOP_FREEZE_NORMAL], // 未冻结
            ['del', '=', 0], // 未删除
            ['is_run', '=', ShopEnum::SHOP_RUN_OPEN], // 未暂停营业
            ['id', 'in', $shop_id]
        ];
        $shops = Shop::where($where)->column('*', 'id');

        foreach ($value as $item) {
            if (!isset($shops[$item])) {
                return '所选店铺中包含暂停营业店铺';
            }
        }

        return true;
    }

}