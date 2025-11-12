<?php


namespace app\admin\logic\content;


use app\common\basics\Logic;
use app\common\model\content\Article;
use Exception;

class ArticleLogic extends Logic
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

            if (!empty($get['title']) and $get['title'])
                $where[] = ['title', 'like', '%'.$get['title'].'%'];

            if (!empty($get['cid']) and is_numeric($get['cid']))
                $where[] = ['cid', '=', $get['cid']];

            if (isset($get['is_notice']) and is_numeric($get['is_notice']))
                $where[] = ['is_notice', '=', $get['is_notice']];

            $model = new Article();
            $lists = $model->field(true)
                ->where($where)
                ->with(['category'])
                ->order('sort', 'asc')
                ->paginate([
                    'page'      => $get['page'],
                    'list_rows' => $get['limit'],
                    'var_page'  => 'page'
                ])
                ->toArray();

            foreach ($lists['data'] as &$item) {
                $item['category']  = $item['category']['name'] ?? '未知';
                $item['is_notice'] = $item['is_notice'] ? '是' : '否';
                $item['is_show']   = $item['is_show'] ? '显示' : '隐藏';
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 文章详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new Article();
        return $model->field(true)->findOrEmpty($id)->toArray();
    }

    /**
     * @Notes: 添加文章
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function add($post)
    {
        try {
            Article::create([
                'cid'       => $post['cid'],
                'title'     => $post['title'],
                'image'     => $post['image'] ?? '',
                'intro'     => $post['intro'] ?? '',
                'content'   => $post['content'] ?? '',
                'visit'     => 0,
                'likes'     => 0,
                'sort'      => $post['sort'] ?? 0,
                'is_notice' => $post['is_notice'],
                'is_show'   => $post['is_show']
            ]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 编辑文章
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function edit($post)
    {
        try {
            Article::update([
                'cid'       => $post['cid'],
                'title'     => $post['title'],
                'image'     => $post['image'] ?? '',
                'intro'     => $post['intro'] ?? '',
                'content'   => $post['content'] ?? '',
                'visit'     => 0,
                'likes'     => 0,
                'sort'      => $post['sort'] ?? 0,
                'is_notice' => $post['is_notice'],
                'is_show'   => $post['is_show']
            ], ['id'=>$post['id']]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 删除
     * @Author: 张无忌
     * @param $id
     * @return bool
     */
    public static function del($id)
    {
        try {
            Article::update([
                'del'         => 1,
                'update_time' => time()
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
            $model = new Article();
            $article = $model->findOrEmpty($id)->toArray();

            Article::update([
                'is_show'     => !$article['is_show'],
                'update_time' => time()
            ], ['id'=>$id]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }
}