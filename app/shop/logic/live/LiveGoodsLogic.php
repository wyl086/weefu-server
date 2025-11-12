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
namespace app\shop\logic\live;

use app\common\basics\Logic;
use app\common\enum\LiveGoodsEnum;
use app\common\model\live\LiveGoods;
use app\common\server\FileServer;
use app\common\server\UrlServer;
use app\common\server\WxMnpLiveServer;
use think\facade\Db;


/**
 * 直播商品逻辑层
 * Class LiveGoodsLogic
 * @package app\adminapi\logic\live
 */
class LiveGoodsLogic extends Logic
{

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
        $where[] = ['del', '=', 0];
        $where[] = ['shop_id', '=', $params['shop_id']];
        if (!empty($params['goods_name'])) {
            $where[] = ['name', 'like', '%' . $params['goods_name'] . '%'];
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

        $count = LiveGoods::where($where)->count();
        $lists = LiveGoods::where($where)
            ->order(['id' => 'desc'])
            ->page($params['page'], $params['limit'])
            ->append(['audit_status_text', 'price_text', 'goods_stock'])
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
    public static function add(array $params)
    {
        try {
            $price = 0;
            $price2 = 0;
            switch ($params['price_type']) {
                case LiveGoodsEnum::PRICE_ONE:
                    $price = $params['price'];
                    break;
                case LiveGoodsEnum::PRICE_RANGE:
                    $price = $params['section_price_start'];
                    $price2 = $params['section_price_end'];
                    break;
                case LiveGoodsEnum::PRICE_DISCOUNT:
                    $price = $params['discount_price_start'];
                    $price2 = $params['discount_price_end'];
                    break;
            }

            $data = [
                'shop_id' => $params['shop_id'],
                'source_type' => LiveGoodsEnum::SOURCE_TYPE_SELF,
                'name' => $params['name'],
                'price_type' => $params['price_type'],
                'price' => $price,
                'price2' => $price2,
                'url' => $params['url'],
                'cover_img_url' => FileServer::wechatLiveMaterial($params['cover_img']),
                'cover_img' => UrlServer::setFileUrl($params['cover_img']),
            ];

            if (isset($params['source_type']) && $params['source_type'] == LiveGoodsEnum::SOURCE_TYPE_GOODS) {
                $data['source_id'] = $params['source_id'];
                $data['source_type'] = LiveGoodsEnum::SOURCE_TYPE_GOODS;
            }

            LiveGoods::create($data);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }



    /**
     * @notes 直播商品详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2023/2/16 10:42
     */
    public static function detail($params)
    {
        $detail = LiveGoods::where(['id' => $params['id'], 'shop_id' => $params['shop_id']])
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

            $where = [
                'id' => $params['id'],
                'shop_id' => $params['shop_id']
            ];
            LiveGoods::where($where)->update([
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