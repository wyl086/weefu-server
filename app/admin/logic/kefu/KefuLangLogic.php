<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\admin\logic\kefu;

use app\common\model\kefu\KefuLang;

/**
 * 客服术语逻辑层
 * Class KefuLangLogic
 * @package app\admin\logic\kefu
 */
class KefuLangLogic
{

    /**
     * @notes 获取列表
     * @param $limit
     * @param $page
     * @return array
     * @throws \think\db\exception\DbException
     * @author cjhao
     * @date 2021/11/29 15:11
     */
    public static function lists(int $limit,int $page)
    {
        $list = KefuLang::where(['shop_id' => 0])->order('sort asc')->paginate([
                'list_rows' => $limit,
                'page'      => $page,
            ]);
        return ['count' => $list->total(), 'lists' => $list->getCollection()];
    }


    /**
     * @notes 新增话术
     * @param $post
     * @return bool
     * @author cjhao
     * @date 2021/11/29 15:54
     */
    public static function add(array $post)
    {
        $kefu_lang = new KefuLang();
        $kefu_lang->title   = $post['title'];
        $kefu_lang->content = $post['content'];
        $kefu_lang->sort    = $post['sort'];
        return $kefu_lang->save();
    }

    /**
     * @notes 编辑话术
     * @param $post
     * @return bool
     * @author cjhao
     * @date 2021/11/29 15:59
     */
    public static function edit(array $post){
        return KefuLang::update($post);
    }


    /**
     * @notes 获取话术
     * @param $id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/11/29 16:02
     */
    public static function detail(int $id){
        return KefuLang::where(['id'=>$id])->find();
    }

    /**
     * @notes 删除话术
     * @param int $id
     * @return bool
     * @author cjhao
     * @date 2021/11/29 16:11
     */
    public static function del(int $id){
        return KefuLang::destroy($id);
    }
}