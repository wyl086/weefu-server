<?php


namespace app\admin\logic\content;


use app\common\basics\Logic;
use app\common\model\content\Help;
use Exception;

class HelpLogic extends Logic
{
    /**
     * 获取帮助分类
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


            $model = new Help();
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
                $item['is_show']   = $item['is_show'] ? '显示' : '隐藏';
            }

            return ['count'=>$lists['total'], 'lists'=>$lists['data']];
        } catch (Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 帮助详细
     * @Author: 张无忌
     * @param $id
     * @return array
     */
    public static function detail($id)
    {
        $model = new Help();
        return $model->field(true)->findOrEmpty($id)->toArray();
    }

    /**
     * @Notes: 添加帮助
     * @Author: 张无忌Help
     * @param $post
     * @return bool
     */
    public static function add($post)
    {
        try {
            Help::create([
                'cid'       => $post['cid'],
                'title'     => $post['title'],
                'image'     => $post['image'] ?? '',
                'intro'     => $post['intro'] ?? '',
                'content'   => $post['content'] ?? '',
                'visit'     => 0,
                'likes'     => 0,
                'sort'      => $post['sort'] ?? 0,
                'is_show'   => $post['is_show']
            ]);

            return true;
        } catch (\Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @Notes: 编辑帮助
     * @Author: 张无忌
     * @param $post
     * @return bool
     */
    public static function edit($post)
    {
        try {
            Help::update([
                'cid'       => $post['cid'],
                'title'     => $post['title'],
                'image'     => $post['image'] ?? '',
                'intro'     => $post['intro'] ?? '',
                'content'   => $post['content'] ?? '',
                'visit'     => 0,
                'likes'     => 0,
                'sort'      => $post['sort'] ?? 0,
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
            Help::update([
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
            $model = new Help();
            $article = $model->findOrEmpty($id)->toArray();

            Help::update([
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