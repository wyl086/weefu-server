<?php


namespace app\api\logic;


use app\common\basics\Logic;
use app\common\model\content\Help;
use app\common\model\content\HelpCategory;
use app\common\server\JsonServer;
use app\common\server\UrlServer;
use think\facade\Db;

class HelpLogic extends Logic
{
    public static function category()
    {
        $where = [
            'del' => 0,
            'is_show' => 1
        ];
        $data = HelpCategory::field('id,name')->where($where)->select()->toArray();
        return $data;
    }

    public static function lists($get)
    {
        $where = [
            ['h.del', '=', 0],
            ['h.is_show', '=', 1],
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

        $model = new Help();

        $list = $model->alias('h')
            ->join('help_category c', 'c.id = h.cid')
            ->field(['h.id', 'h.title', 'h.intro', 'h.image', 'h.visit', 'h.likes', 'h.content', 'h.create_time'])
            ->where($where)
            ->order($order)
            ->page($get['page_no'], $get['page_size'])
            ->select()
            ->toArray();

        $count = $model->alias('h')->join('help_category c', 'c.id = h.cid')->where($where)->count();

        $more = is_more($count, $get['page_no'], $get['page_size']);

        $data = [
            'list'          => $list,
            'page_no'       => $get['page_no'],
            'page_size'     => $get['page_size'],
            'count'         => $count,
            'more'          => $more
        ];
        return $data;
    }

    public static function detail($id)
    {
        $help =  Help::field('id,title,create_time,visit,content')->where('id', $id)->findOrEmpty();

        if($help->isEmpty()) {
            $help = [];
        }else{
            $help->visit = $help->visit + 1;
            $help->save();
            $help = $help->toArray();
        }

        $recommend_list = Db::name('help')
            ->where([['del','=','0'], ['id','<>',$id]])
            ->field('id,title,image,visit')
            ->order('visit desc')
            ->limit(5)
            ->select()
            ->toArray();

        foreach ($recommend_list as $key => $recommend){
            $recommend_list[$key]['image'] = empty($recommend['image']) ? "" : UrlServer::getFileUrl($recommend['image']);
        }

        $help['recommend_list'] = $recommend_list;
        return $help;
    }
}