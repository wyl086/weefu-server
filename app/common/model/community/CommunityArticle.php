<?php


namespace app\common\model\community;


use app\common\basics\Models;
use app\common\enum\CommunityArticleEnum;
use app\common\model\goods\Goods;
use app\common\model\shop\Shop;
use app\common\model\user\User;

/**
 * 种草社区文章
 * Class CommunityCategory
 * @package app\common\model\content
 */
class CommunityArticle extends Models
{


    /**
     * @notes 关联作者信息
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2022/4/29 17:59
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


    /**
     * @notes 关联文章图片
     * @return \think\model\relation\HasMany
     * @author 段誉
     * @date 2022/4/29 11:29
     */
    public function images()
    {
        return $this->hasMany(CommunityArticleImage::class, 'article_id', 'id');
    }


    /**
     * @notes 关联话题
     * @return \think\model\relation\HasOne
     * @author 段誉
     * @date 2022/5/6 14:37
     */
    public function topic()
    {
        return $this->hasOne(CommunityTopic::class, 'id', 'topic_id');
    }


    /**
     * @notes 关联商品相关信息
     * @param $value
     * @param $data
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 10:05
     */
    public function getGoodsDataAttr($value, $data)
    {
        if (empty($data['goods'])) {
            return [];
        }

        $result = Goods::field(['id', 'name', 'image'])
            ->where(['status' => 1, 'del' => 0])
            ->whereIn('id', $data['goods'])
            ->select();

        return $result;
    }


    /**
     * @notes 关联店铺信息
     * @param $value
     * @param $data
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/5/6 17:55
     */
    public function getShopDataAttr($value, $data)
    {
        if (empty($data['shop'])) {
            return [];
        }

        $result = Shop::field(['id', 'name', 'logo'])
            ->where(['is_freeze' => 0, 'del' => 0, 'is_run' => 1])
            ->whereIn('id', $data['shop'])
            ->select();

        return $result;
    }



    /**
     * @notes 状态属性
     * @param $value
     * @param $data
     * @return string|string[]
     * @author 段誉
     * @date 2022/5/9 18:13
     */
    public function getStatusDescAttr($value, $data)
    {
        return  CommunityArticleEnum::getStatusDesc($data['status']);
    }


    /**
     * @notes 文章所选店铺格式化
     * @param $value
     * @param $data
     * @return false|string|string[]
     * @author 段誉
     * @date 2022/4/29 10:55
     */
    public function getShopAttr($value, $data)
    {
        return $this->formattingData($value, 'explode');
    }

    /**
     * @notes 文章所选店铺格式化
     * @param $value
     * @param $data
     * @return false|string|string[]
     * @author 段誉
     * @date 2022/4/29 10:55
     */
    public function setShopAttr($value, $data)
    {
        return $this->formattingData($value, 'implode');
    }


    /**
     * @notes 文章所选商品格式化
     * @param $value
     * @param $data
     * @return false|string|string[]
     * @author 段誉
     * @date 2022/4/29 10:55
     */
    public function getGoodsAttr($value, $data)
    {
        return $this->formattingData($value, 'explode');
    }


    /**
     * @notes 文章所选商品格式化
     * @param $value
     * @param $data
     * @return false|string|string[]
     * @author 段誉
     * @date 2022/4/29 10:55
     */
    public function setGoodsAttr($value, $data)
    {
        return $this->formattingData($value, 'implode');
    }


    /**
     * @notes 格式化数据
     * @param $params
     * @param $operation
     * @return array|string
     * @author 段誉
     * @date 2022/5/6 9:50
     */
    protected function formattingData($params, $operation)
    {
        if (empty($params) || !in_array($operation, ['explode', 'implode'])) {
            return $operation == 'explode' ? [] : $params;
        }

        if ('explode' == $operation) {
            return array_map('intval', explode(',', $params));
        }
        return implode(',', $params);
    }


    /**
     * @notes 增加点赞数量
     * @param $id
     * @return mixed
     * @author 段誉
     * @date 2022/5/9 15:34
     */
    public static function incLike($id)
    {
        return self::where(['id' => $id])->inc('like')->update();
    }


    /**
     * @notes 减少点赞数量
     * @param $id
     * @return mixed
     * @author 段誉
     * @date 2022/5/9 15:37
     */
    public static function decLike($id)
    {
        $where = [
            ['id', '=', $id],
            ['like', '>=', 1]
        ];
        return self::where($where)->dec('like')->update();
    }

}