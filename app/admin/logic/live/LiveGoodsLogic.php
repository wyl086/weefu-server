<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\admin\logic\live;

use app\common\basics\Logic;
use app\common\enum\LiveGoodsEnum;
use app\common\model\live\LiveGoods;
use app\common\server\UrlServer;
use app\common\server\WxMnpLiveServer;
use think\facade\Db;


/**
 * 直播商品逻辑层
 * Class LiveGoodsLogic
 * @package app\admin\logic\live
 */
class LiveGoodsLogic extends Logic
{

    /**
     * @notes 查询条件
     * @param $params
     * @return array
     * @author 段誉
     * @date 2023/2/16 21:17
     */
    public static function listsQuery($params)
    {
        $where[] = ['del', '=', 0];
        if (!empty($params['goods_name'])) {
            $where[] = ['name', 'like', '%' . $params['goods_name'] . '%'];
        }
        if (!empty($params['shop_id'])) {
            $where[] = ['shop_id', '=', $params['shop_id']];
        }

        if (!empty($params['status'])) {
            if ($params['status'] == 'ing') {
                $where[] = ['sys_audit_status', 'in', [
                    LiveGoodsEnum::SYS_AUDIT_STATUS_WAIT_PLATFORM,
                    LiveGoodsEnum::SYS_AUDIT_STATUS_WAIT_WECHAT
                ]];
            }
            if ($params['status'] == 'success') {
                $where[] = ['sys_audit_status', '=', LiveGoodsEnum::SYS_AUDIT_STATUS_SUCCESS];
            }
            if ($params['status'] == 'fail') {
                $where[] = ['sys_audit_status', '=', LiveGoodsEnum::SYS_AUDIT_STATUS_FAIL];
            }
        }
        return $where;
    }


    /**
     * @notes 直播商品列表
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2023/2/15 19:00
     */
    public static function lists($params)
    {
        $where = self::listsQuery($params);

        $count = LiveGoods::where($where)->count();
        $lists = LiveGoods::with(['shop'])->where($where)
            ->order(['id' => 'desc'])
            ->page($params['page'], $params['limit'])
            ->append(['audit_status_text', 'price_text'])
            ->select()->toArray();

        foreach ($lists as &$item) {
            $item['cover_img'] = UrlServer::getFileUrl($item['cover_img']);
        }
        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * @notes 添加直播商品
     * @param array $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/15 18:26
     */
    public static function audit(array $params)
    {
        Db::startTrans();
        try {
            if ($params['status'] == LiveGoodsEnum::SYS_AUDIT_STATUS_FAIL && empty($params['audit_remark'])) {
                throw new \Exception('审核不通过请填写审核原因');
            }

            $liveGoods = LiveGoods::findOrEmpty($params['id'])->toArray();
            if ($liveGoods['sys_audit_status'] > LiveGoodsEnum::SYS_AUDIT_STATUS_WAIT_PLATFORM) {
                throw new \Exception('当前商品待微信审核或已审核完成');
            }

            // 更新信息
            $update_data = [
                'sys_audit_status' => $params['status'],
                'audit_remark' => $params['audit_remark'] ?? '',
            ];

            if ($params['status'] == LiveGoodsEnum::SYS_AUDIT_STATUS_WAIT_WECHAT) {
                $goods_res = self::addWechatGoods($liveGoods);
                $update_data['wx_goods_id'] = $goods_res['goodsId'];
                $update_data['wx_audit_id'] = $goods_res['auditId'];
            }

            // 提交审核，通过则待微信审核
            LiveGoods::where(['id' => $params['id']])->update($update_data);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 提交微信审核
     * @param $goods
     * @return bool
     * @author 段誉
     * @date 2023/2/17 10:06
     */
    public static function addWechatGoods($goods)
    {
        $data = [
            'coverImgUrl' => $goods['cover_img_url'],
            'name' => $goods['name'],
            'priceType' => $goods['price_type'],
            'price' => $goods['price'],
            'price2' => $goods['price2'],
            'url' => $goods['url'],
        ];
        return (new WxMnpLiveServer())->handle('addAndAuditGoods', $data);
    }


    /**
     * @notes 直播商品详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2023/2/16 10:42
     */
    public static function detail($id)
    {
        $detail = LiveGoods::where(['id' => $id])
            ->append(['price_type_text', 'price_tips', 'source_type_text', 'audit_status_text'])
            ->findOrEmpty()->toArray();
        $detail['cover_img'] = UrlServer::getFileUrl($detail['cover_img']);
        return $detail;
    }


    /**
     * @notes 删除直播商品
     * @param array $params
     * @return bool|string
     * @author 段誉
     * @date 2023/2/16 10:37
     */
    public static function del(array $params)
    {
        Db::startTrans();
        try {
            $goods = LiveGoods::findOrEmpty($params['id'])->toArray();
            if ($goods['sys_audit_status'] < LiveGoodsEnum::SYS_AUDIT_STATUS_WAIT_WECHAT) {
                throw new \Exception('当前商品暂不可删除');
            }

            LiveGoods::where(['id' => $params['id']])->update([
                'del' => 1,
                'update_time' => time()
            ]);

            // 删除微信商品库
            if (!empty($goods['wx_goods_id'])) {
                (new WxMnpLiveServer())->handle('delGoods', $goods['wx_goods_id']);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }


}