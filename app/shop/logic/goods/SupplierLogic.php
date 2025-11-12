<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\shop\logic\goods;


use app\common\basics\Logic;
use app\common\model\goods\Supplier;


/**
 * 供应商
 * Class SupplierLogic
 * @package app\admin\logic
 */
class SupplierLogic extends Logic
{

    /**
     * Notes: 列表
     * @param $shop_id
     * @param $get
     * @author 段誉(2021/4/15 10:53)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function lists($shop_id, $get)
    {
        $where[] = ['del', '=', 0];
        $where[] = ['shop_id', '=', $shop_id];
        if(isset($get['keyword']) && $get['keyword']){
            $where[] = ['name','like','%'.$get['keyword'].'%'];
        }

        $result = Supplier::where($where)
            ->paginate([
                'list_rows'=> $get['limit'],
                'page'=> $get['page']
            ]);

        return ['count' => $result->total(), 'lists' => $result->getCollection()];
    }


    /**
     * Notes: 添加
     * @param $post
     * @author 段誉(2021/4/15 10:54)
     * @return Supplier|\think\Model
     */
    public static function add($shop_id, $post)
    {
        return Supplier::create([
            'shop_id'  => $shop_id,
            'name'     => $post['name'],
            'contact'  => $post['contact'],
            'mobile'   => $post['mobile'],
            'address'  => $post['address'],
            'remark'   => $post['remark'] ?? '',
        ]);
    }


    /**
     * Notes: 编辑
     * @param $post
     * @author 段誉(2021/4/15 10:54)
     * @return Supplier
     */
    public static function edit($shop_id, $post)
    {
        return Supplier::update([
            'name'     => $post['name'],
            'contact'  => $post['contact'],
            'mobile'   => $post['mobile'],
            'address'  => $post['address'],
            'remark'   => $post['remark'] ?? '',
        ], ['id' => $post['id'], 'shop_id' => $shop_id]);
    }


    /**
     * Notes: 删除
     * @param $id
     * @author 段誉(2021/4/15 10:54)
     * @return Supplier
     */
    public static function del($shop_id, $id)
    {
        return Supplier::update(['del' => 1], ['id' => $id, 'shop_id' => $shop_id]);
    }

}