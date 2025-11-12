<?php
namespace app\shop\logic\goods;

use app\common\basics\Logic;
use app\common\model\goods\GoodsComment;
use app\common\model\user\UserLevel;
use app\common\server\UrlServer;

class CommentLogic extends Logic
{
    public static function lists($get)
    {
        $where = [
            ['gc.shop_id', '=', $get['shop_id']],
            ['gc.del', '=', 0],
        ];

        if($get['type'] == 0) { // 待回复
            $where[] = ['reply', '=', ''];
        }else{ // 已回复
            $where[] = ['reply', '<>', ''];
        }

        // 评价信息
        if(isset($get['search_word']) && !empty($get['search_word'])) {
            switch($get['search_type']) {
                case 'name':
                    $where[] = ['g.name', 'like', '%'. trim($get['search_word']) . '%'];
                    break;
                case 'sn':
                    $where[] = ['u.sn', '=', trim($get['search_word'])];
                    break;
                case 'nickname':
                    $where[] = ['u.nickname', '=', trim($get['search_word'])];
                    break;
            }
        }

        // 评价等级
        if(isset($get['goods_comment']) && !empty($get['goods_comment'])) {
            switch ($get['goods_comment']) {
                case 1:
                    $where[] = ['gc.goods_comment', '>', 3];
                    break;
                case 2:
                    $where[] = ['gc.goods_comment', '=', 3];
                    break;
                case 3:
                    $where[] = ['gc.goods_comment', '<', 3];
                    break;
            }
        }

        // 显示状态
        if(isset($get['status']) && !empty($get['status'])) {
            switch ($get['status']) {
                case 1:
                    $where[] = ['gc.status', '=', 1];
                    break;
                case 2: // 隐藏状态  前端不使用0的原因：empty()时0会被认为false
                    $where[] = ['gc.status', '=', 0];
                    break;
            }
        }

        // 日期范围
        if(isset($get['start_end']) && !empty($get['start_end'])) {
            $arr = explode('~', $get['start_end']);
            $start_time = strtotime($arr[0]);
            $end_time = strtotime($arr[1]);
            $where[] = ['gc.create_time', '>=', $start_time];
            $where[] = ['gc.create_time', '<=', $end_time];
        }

        $lists = GoodsComment::alias('gc')
            ->with(['goods_comment_image'])
            ->field('gc.id,gc.goods_comment,gc.goods_comment as goods_comment_desc,gc.comment,gc.reply,gc.status,gc.status as status_desc,gc.create_time,u.sn,u.nickname,u.avatar,u.level,g.name as goods_name,g.image as goods_image,gi.image as item_image,gi.spec_value_str')
            ->leftJoin('user u', 'u.id=gc.user_id')
            ->leftJoin('goods g', 'g.id=gc.goods_id')
            ->leftJoin('goods_item gi', 'gi.id=gc.item_id')
            ->where($where)
            ->order('gc.create_time', 'desc')
            ->page($get['page'], $get['limit'])
            ->select()
            ->toArray();

        $count = GoodsComment::alias('gc')
            ->field('gc.id,gc.goods_comment,gc.goods_comment as goods_comment_desc,gc.comment,gc.reply,gc.status,gc.status as status_desc,gc.create_time,u.sn,u.nickname,u.avatar,u.level,g.name as goods_name,g.image as goods_image,gi.image as item_image,gi.spec_value_str')
            ->leftJoin('user u', 'u.id=gc.user_id')
            ->leftJoin('goods g', 'g.id=gc.goods_id')
            ->leftJoin('goods_item gi', 'gi.id=gc.item_id')
            ->where($where)
            ->count();

        $levelArr = UserLevel::where('del', 0)->column('name', 'id');

        foreach($lists as &$item) {
            // 类型
            $item['type'] = $get['type'];
            // 头像
            $item['avatar'] = UrlServer::getFileUrl($item['avatar']);
            // 会员等级
            $item['levelName'] = $levelArr[$item['level']] ?? '无等级';
            // 评价图片
            $item['comment_image'] = array_column($item['goods_comment_image'], 'uri');
            foreach($item['comment_image'] as $key => $subItem) {
                $item['comment_image'][$key] = UrlServer::getFileUrl($subItem);
            }
            $item['goods_image'] = UrlServer::getFileUrl($item['goods_image']);
            $item['item_image'] = empty($item['item_image']) ? '' : UrlServer::getFileUrl($item['item_image']);
        }

        return [
            'count' => $count,
            'lists' => $lists
        ];
    }

    public static function reply($post)
    {
        try{
            GoodsComment::where('id', $post['id'])->update([
                'reply' => trim($post['reply']),
                'update_time' => time()
            ]);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}