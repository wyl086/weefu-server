<?php


namespace app\api\logic;


use app\common\basics\Logic;
use app\common\model\content\Article;
use app\common\model\content\ArticleCategory;
use app\common\server\UrlServer;
use think\Db;

class ArticleLogic extends Logic
{
    /**
     * @Notes: 文章分类
     * @Author: 张无忌
     * @param $get
     * @return array
     */
    public static function category($get)
    {
        try {
            $model = new ArticleCategory();
            return $model->field(['id', 'name'])
                ->where([
                    ['del', '=', 0],
                    ['is_show', '=', 1]
                ])->select()->toArray();

        } catch (\Exception $e) {
            return ['error'=>$e->getMessage()];
        }
    }

    /**
     * @Notes: 文章列表
     * @Author: 张无忌
     * @param $get
     * @return array
     */
    public static function lists($get)
    {
        try {
            $where = [
                ['a.del', '=', 0],
                ['a.is_show', '=', 1],
                ['c.del', '=', 0],
                ['c.is_show', '=', 1],
            ];
            if(isset($get['cid']) && !empty($get['cid'])) {
                $where[] = ['cid', '=', $get['cid']];
            }

            $order = [
                'sort' => 'asc',
                'id' => 'desc'
            ];

            $model = new Article();

            $count = $model->alias('a')->join('article_category c', 'c.id = a.cid')->where($where)->count();

            $list =  $model->alias('a')
                ->join('article_category c', 'c.id = a.cid')
                ->field(['a.id', 'a.title', 'a.image', 'a.visit', 'a.likes','a.intro', 'a.content', 'a.create_time'])
                ->where($where)
                ->order($order)
                ->page($get['page_no'], $get['page_size'])
                ->select()
                ->toArray();

            $more = is_more($count, $get['page_no'], $get['page_size']);

            $data = [
                'list'          => $list,
                'page_no'       => $get['page_no'],
                'page_size'     => $get['page_size'],
                'count'         => $count,
                'more'          => $more
            ];
            return $data;

        } catch (\Exception $e) {
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
        $article =  Article::field('id,title,create_time,visit,content')->where('id', $id)->findOrEmpty();
        if($article->isEmpty()) {
            $article = [];
        }else{
            $article->visit = $article->visit + 1;
            $article->save();
            $article = $article->toArray();
        }
        return $article;
    }
}