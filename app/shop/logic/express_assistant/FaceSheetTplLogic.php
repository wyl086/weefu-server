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
use app\common\model\Express;
use app\common\model\face_sheet\FaceSheetTemplate;
use Exception;

/**
 * 面单模板
 * Class FaceSheetTplLogic
 * @package app\shop\logic\express_assistant
 */
class FaceSheetTplLogic extends Logic
{

    /**
     * @notes 获取电子面单模板列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 15:29
     */
    public static function lists($get, $shop_id)
    {
        $where = ['shop_id' => $shop_id];
        $model = new FaceSheetTemplate();
        $count = $model->where($where)->count('id');
        $lists = $model->order('id', 'desc')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->select();

        foreach ($lists as &$item) {
            $item['express'] = Express::where(['id'=>$item['express_id']])->value('name') ?? '未知';
        }

        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * @notes 获取电子面单模板详细
     * @param $id
     * @param $shop_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 15:34
     */
    public static function detail($id, $shop_id)
    {
        return FaceSheetTemplate::where(['id' => intval($id), 'shop_id' => $shop_id])->find();
    }


    /**
     * @notes 新增电子面单模板
     * @param $post
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 15:31
     */
    public static function add($post, $shop_id)
    {
        try {
            FaceSheetTemplate::create([
                'shop_id'     => $shop_id,
                'express_id'  => $post['express_id'],
                'name'        => $post['name'],
                'template_id' => $post['template_id'],
                'partner_id'  => $post['partner_id'],
                'partner_key' => $post['partner_key'],
                'net'         => $post['net'],
                'pay_type'    => $post['pay_type'],
                'create_time' => time(),
                'update_time' => time()
            ]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * @notes 编辑电子面单模板
     * @param $post
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 15:32
     */
    public static function edit($post, $shop_id)
    {
        try {
            FaceSheetTemplate::update([
                'express_id'  => $post['express_id'],
                'name'        => $post['name'],
                'template_id' => $post['template_id'],
                'partner_id'  => $post['partner_id'],
                'partner_key' => $post['partner_key'],
                'net'         => $post['net'],
                'pay_type'    => $post['pay_type'],
                'update_time' => time()
            ], ['id' => $post['id'], 'shop_id' => $shop_id]);

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * @notes 删除电子面单模板
     * @param $id
     * @param $shop_id
     * @return bool|string
     * @author 段誉
     * @date 2023/2/13 15:32
     */
    public static function del($id, $shop_id)
    {
        try {
            FaceSheetTemplate::where(['shop_id' => $shop_id, 'id' => $id])
                ->delete();
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * @notes 快递公司
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 15:13
     */
    public static function allExpress()
    {
        return Express::where(['del' => 0])->select();
    }


    /**
     * @notes 所有电子面单模板
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/13 16:02
     */
    public static function allTpl($shop_id)
    {
        $model = new FaceSheetTemplate();
        return $model->where(['shop_id' => $shop_id])
            ->order('id', 'desc')
            ->select();
    }

}