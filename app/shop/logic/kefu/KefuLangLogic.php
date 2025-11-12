<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\shop\logic\kefu;

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
    public static function lists(int $shop_id,int $limit,int $page)
    {
        $list = KefuLang::where(['shop_id'=>$shop_id])->order('sort asc')->paginate([
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
    public static function add(int $shop_id,array $post)
    {
        $kefu_lang = new KefuLang();
        $kefu_lang->shop_id = $shop_id;
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
    public static function detail(int $shop_id,int $id){
        return KefuLang::where(['id'=>$id,'shop_id'=>$shop_id])->find();
    }

    /**
     * @notes 删除话术
     * @param int $id
     * @return bool
     * @author cjhao
     * @date 2021/11/29 16:11
     */
    public static function del(int $shop_id,int $id){
        return KefuLang::where(['id'=>$id,'shop_id'=>$shop_id])->delete();
    }
}