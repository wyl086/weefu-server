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

namespace app\shop\logic\express_assistant;


use app\common\basics\Logic;
use app\common\model\face_sheet\FaceSheetSender;
use app\common\server\AreaServer;
use Exception;

/**
 * 发件人模板
 * Class FaceSheetSenderLogic
 * @package app\shop\logic\express_assistant
 */
class FaceSheetSenderLogic extends Logic
{

    /**
     * @notes 获取发件人列表
     * @param $get
     * @param $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 16:41
     */
    public static function lists($get, $shop_id)
    {
        $where = ['shop_id' => $shop_id];
        $model = new FaceSheetSender();
        $count = $model->where($where)->count('id');
        $lists = $model->where($where)->order('id', 'desc')
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $item['region'] = AreaServer::getAddress([
                $item['province_id'],
                $item['city_id'],
                $item['district_id'],
            ]);
        }

        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * @notes 所有发件人模板
     * @param $shop_id
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 16:41
     */
    public static function allSender($shop_id)
    {
        $model = new FaceSheetSender();
        return $model->where(['shop_id' => $shop_id])
            ->order('id', 'desc')
            ->select();
    }


    /**
     * @notes 获取发件人模板详细
     * @param $id
     * @param $shop_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 16:40
     */
    public static function detail($id, $shop_id)
    {
        return FaceSheetSender::where(['id'=>$id, 'shop_id' => $shop_id])->find();
    }


    /**
     * @notes 新增发件人模板
     * @param $post
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 16:40
     */
    public static function add($post, $shop_id)
    {
        try {
            FaceSheetSender::create([
                'shop_id'     => $shop_id,
                'name'        => $post['name'],
                'mobile'      => $post['mobile'],
                'province_id' => $post['province_id'],
                'city_id'     => $post['city_id'],
                'district_id' => $post['district_id'],
                'address'     => $post['address'],
                'create_time' => time(),
                'update_time' => time(),
            ]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * @notes 编辑发件人模板
     * @param $post
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 16:40
     */
    public static function edit($post, $shop_id)
    {
        try {
            FaceSheetSender::update([
                'name'        => $post['name'],
                'mobile'      => $post['mobile'],
                'province_id' => $post['province_id'],
                'city_id'     => $post['city_id'],
                'district_id' => $post['district_id'],
                'address'     => $post['address'],
                'update_time' => time(),
            ], ['id'=>$post['id'], 'shop_id' => $shop_id]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * @notes 删除发件人模板
     * @param $id
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 16:40\
     */
    public static function del($id, $shop_id)
    {
        try {
            FaceSheetSender::where(['shop_id' => $shop_id, 'id' => $id])
                ->delete();
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}