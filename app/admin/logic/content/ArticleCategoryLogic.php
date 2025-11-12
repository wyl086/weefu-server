<?php


namespace app\admin\logic\content;


use app\common\basics\Logic;
use app\common\model\content\ArticleCategory;
use Exception;

class ArticleCategoryLogic extends Logic
{
    /**
     * 获取文章分类
     * @param $get
     * @return array
     */
    public static function lists($get)
    {
        try {
            $where = [
                ['del', '=', 0]
            ];

            $model = new ArticleCategory();
            $lists = $model->field(true)
                ->where($where)
                ->order('id', 'desc')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page'  => 'page'
                ])
                ->toArray();

            foreach ($lists['data'] as &$item) {
                $item['is_show'] = $item['is_show'] ? '启用' : '停用';
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 获取文章分类
     * @Author: 张无忌
     * @return array
     */
    public static function getCategory()
    {
        try {
            $model = new ArticleCategory();
            return $model->field(true)
                ->where(['del'=>0, 'is_show'=>1])
                ->order('id', 'desc')
                ->select()
                ->toArray();

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取文章分类详细
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new ArticleCategory();
        return $model->field(true)->findOrEmpty($id)->toArray();
    }

    /**
     * 添加文章分类
     * @param $post
     * @return bool
     */
    public static function add($post)
    {
        try {

            ArticleCategory::create([
                'name'    => $post['name'],
                'is_show' => $post['is_show']
            ]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 编辑文章分类
     * @param $post
     * @return bool
     */
    public static function edit($post)
    {
        try {

            ArticleCategory::update([
                'name'    => $post['name'],
                'is_show' => $post['is_show']
            ], ['id'=>$post['id']]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 删除文章分类
     * @param $id
     * @return bool
     */
    public static function del($id)
    {
        try {

            ArticleCategory::update([
                'del' => 1
            ], ['id'=>$id]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 隐藏
     * @Author: 张无忌
     * @param $id
     * @return bool
     */
    public static function hide($id)
    {
        try {
            $model = new ArticleCategory();
            $category = $model->findOrEmpty($id)->toArray();

            ArticleCategory::update([
                'is_show'     => !$category['is_show'],
                'update_time' => time()
            ], ['id'=>$id]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
}