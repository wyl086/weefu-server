<?php
namespace app\admin\logic\user;

use app\common\basics\Logic;
use app\common\model\user\UserTag;
use app\common\model\user\User;

class TagLogic extends Logic
{
    public static function lists($get)
    {
        $lists = UserTag::where(['del'=>0])->page($get['page'], $get['limit'])->select()->toArray();
        $count = UserTag::where(['del'=>0])->count();
        return [
            'lists' => $lists,
            'count' => $count
        ];
    }

    public static function add($post)
    {
        try{
            // 判断新标签名称是否已被占用
            $userTag = UserTag::where(['name'=>trim($post['name']), 'del'=>0])->findOrEmpty();
            if(!$userTag->isEmpty()) {
                throw new \think\Exception('标签名称已被使用，请换个名称重试');
            }
            $time = time();
            $data = [
                'name' => trim($post['name']),
                'remark' => trim($post['remark']),
                'create_time' => $time,
                'update_time' => $time,
                'del' => 0
            ];
            UserTag::create($data);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function detail($id)
    {
       $userTag =  UserTag::where(['id'=>$id])->findOrEmpty();
       if($userTag->isEmpty()) {
           return [];
       }
       return $userTag->toArray();
    }

    public static function edit($post)
    {
        try{
            $userTag = UserTag::where([
                ['name', '=', trim($post['name'])],
                ['id', '<>', trim($post['id'])],
            ])->findOrEmpty();
            if(!$userTag->isEmpty()){
                throw new \think\Exception('标签名称已被使用，请换个名称重试');
            }
            $data = [
                'id' => $post['id'],
                'name' => trim($post['name']),
                'remark' => trim($post['remark']),
                'update_time' => time()
            ];
            UserTag::update($data);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function del($id)
    {
        try{
            // 查看是否有会员正在使用该标签，若有则不允许删除
            $users = User::whereFindInSet('tag_ids',$id)->where('del',0)->count();
            if($users) {
                throw new \think\Exception('有会员正在使用该标签，无法删除');
            }
            $data = [
                'id' => $id,
                'update_time' => time(),
                'del' => 1
            ];
            UserTag::update($data);
            return true;
        }catch(\Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function getTagList()
    {
        $tagArr = UserTag::field('id,name')
            ->where('del', 0)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        return $tagArr;
    }
}