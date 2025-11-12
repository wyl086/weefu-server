<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic\shop;


use app\common\basics\Logic;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopApply;
use app\common\model\shop\ShopCategory;
use Exception;

class CategoryLogic extends Logic
{
    /**
     * NOTE: 主营类目列表
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

            $model = new ShopCategory();
            $lists = $model->field(true)
                ->where($where)
                ->order('sort', 'desc')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page' => 'page'
                ])
                ->toArray();

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * NOTE: 主营类目详细
     * @author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new ShopCategory();
        return $model->field(true)->findOrEmpty((int)$id)->toArray();
    }

    /**
     * NOTE: 获取主营类目
     * @author: 张无忌
     * @return array
     */
    public static function getCategory()
    {
        try {
            $model = new ShopCategory();
            return $model->field(true)
                ->where('del', 0)
                ->order('id', 'desc')
                ->order('sort', 'desc')
                ->select()->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * NOTE: 新增主营类目
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function add($post)
    {
        try {
            ShopCategory::create([
                'name'  => $post['name'],
                'image' => $post['image'] ?? '',
                'sort'  => $post['sort'] ?? 0
            ]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 编辑主营类目
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function edit($post)
    {
        try {
            ShopCategory::update([
                'name'  => $post['name'],
                'image' => $post['image'] ?? '',
                'sort'  => $post['sort'] ?? 0
            ], ['id'=>(int)$post['id']]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 删除主营类目
     * @author: 张无忌
     * @param $id
     * @return bool
     */
    public static function del($id)
    {
        try {
            $shopModel      = new Shop();
            $shopApplyModel = new ShopApply();

            $shop  = $shopModel->where(['cid'=>(int)$id, 'del'=>0])->findOrEmpty()->toArray();
            $apply = $shopApplyModel->where(['cid'=>(int)$id, 'del'=>0])->findOrEmpty()->toArray();

            if ($shop or $apply) {
                static::$error = '类目已被使用,不允许删除';
                return false;
            }

            ShopCategory::update([
                'del'         => 1,
                'create_time' => time()
            ], ['id'=>(int)$id]);

            return true;
        }  catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
}