<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\logic\goods;


use app\common\basics\Logic;
use app\common\enum\GoodsEnum;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsColumn;
use app\common\model\goods\GoodsImage;
use app\common\model\goods\GoodsItem;
use app\common\model\goods\GoodsSpec;
use app\common\model\goods\GoodsSpecValue;
use app\common\model\goods\Supplier;
use app\common\server\UrlServer;
use think\facade\Db;
use app\common\model\seckill\SeckillGoods;


/**
 * 商品管理-逻辑
 * Class GoodsLogic
 * @package app\shop\logic\goods
 */
class GoodsLogic extends Logic
{
    /*
     * 商品统计
     */
    public static function statistics($get = []) {
        $where = [
            ['del', '<>', GoodsEnum::DEL_TRUE]
        ];

        if(isset($get['goods_column_id']) && $get['goods_column_id'] != '') {
            $where[] = ['column_ids', '=', $get['goods_column_id']];
        }
        
        return [
            // 销售中商品(含库存预警商品)
            // 销售状态：上架中；删除状态：正常； 审核状态： 审核通过
            'sell'      => Goods::where($where)
                ->where('del', GoodsEnum::DEL_NORMAL)
                ->where('status', GoodsEnum::STATUS_SHELVES)
                ->where('audit_status', GoodsEnum::AUDIT_STATUS_OK)
                ->count(),
            // 仓库中商品
            // 销售状态：仓库中；删除状态：正常； 审核状态： 审核通过
            'warehouse' => Goods::where($where)
                ->where('del', GoodsEnum::DEL_NORMAL)
                ->where('status', GoodsEnum::STATUS_SOLD_OUT)
                ->where('audit_status', GoodsEnum::AUDIT_STATUS_OK)
                ->count(),
            // 回收站商品
            // 销售状态：任意；删除状态：回收站； 审核状态： 审核通过
            'recycle'   => Goods::where($where)
                ->where('del', GoodsEnum::DEL_RECYCLE)
                ->where('audit_status', GoodsEnum::AUDIT_STATUS_OK)
                ->count(),
            // 待审核商品
            // 销售状态：任意；删除状态：排除已删除； 审核状态： 待审核
            'audit_stay' => Goods::where($where)
                ->where('del', '<>', GoodsEnum::DEL_TRUE)
                ->where('audit_status', GoodsEnum::AUDIT_STATUS_STAY)
                ->count(),
            // 审核未通过商品
            // 销售状态：任意；删除状态：排除已删除； 审核状态： 审核未通过
            'audit_refuse'=> Goods::where($where)
                ->where('del', '<>', GoodsEnum::DEL_TRUE)
                ->where('audit_status', GoodsEnum::AUDIT_STATUS_REFUSE)
                ->count(),
        ];
    }

    /**
     * Notes: 列表
     * @param $get
     * @author 段誉(2021/4/15 10:53)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function lists($get)
    {
        $where = [];
        if(isset($get['shop_name']) && !($get['shop_name'] == '')) {
            $where[] = ['s.name','like','%'.$get['shop_name'].'%'];
        }

        if(isset($get['goods_name']) && !($get['goods_name'] == '')) {
            $where[] = ['g.name','like','%'.$get['goods_name'].'%'];
        }
        if(!empty($get['platform_cate_id'])) {
            $where[] = ['g.first_cate_id|g.second_cate_id|g.third_cate_id','=', $get['platform_cate_id']];
        }

        if(isset($get['goods_type']) && $get['goods_type'] != '') {
            $where[] = ['g.type','=', $get['goods_type']];
        }

        if(isset($get['goods_column_id']) && $get['goods_column_id'] != '') {
            $where[] = ['g.column_ids', '=', $get['goods_column_id']];
        }

        $type = $get['type'] ?? 0;

        switch ($type) {
            case 1:     //销售中
                $where[] = ['g.status', '=', GoodsEnum::STATUS_SHELVES];//上架
                $where[] = ['g.del', '=', GoodsEnum::DEL_NORMAL];
                $where[] = ['g.audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                break;
            case 2:      //仓库中
                $where[] = ['g.status', '=', GoodsEnum::STATUS_SOLD_OUT];//下架
                $where[] = ['g.del', '=', GoodsEnum::DEL_NORMAL];
                $where[] = ['g.audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                break;
            case 3:     //回收站
                $where[] = ['g.del', '=', GoodsEnum::DEL_RECYCLE];
                $where[] = ['g.audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                break;
            case 4:  //待审核
                $where[] = ['g.del', '<>', GoodsEnum::DEL_TRUE];
                $where[] = ['g.audit_status', '=', GoodsEnum::AUDIT_STATUS_STAY];
                break;
            case 5: //审核未通过
                $where[] = ['g.del', '<>', GoodsEnum::DEL_TRUE];
                $where[] = ['g.audit_status', '=', GoodsEnum::AUDIT_STATUS_REFUSE];
                break;
            default:
                $where[] = ['g.del', '=', GoodsEnum::DEL_NORMAL];
        }

        $lists = Goods::alias('g')
            ->field('g.id, g.image, g.spec_type, g.name, g.min_price, g.max_price, g.sales_actual, g.stock, g.sort_weight, g.create_time, g.column_ids, g.audit_status, g.audit_remark,s.id as shop_id, s.name as shop_name, s.logo as shop_logo, s.type as shop_type')
            ->leftJoin('Shop s', 's.id=g.shop_id')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->order('g.create_time', 'desc')
            ->select();
        $count = Goods::alias('g')->leftJoin('shop s', 's.id = g.shop_id')->where($where)->count();
        foreach($lists as &$item) {
            $item['price'] = $item['spec_type'] == 1 ? $item["min_price"] : $item["min_price"] . " ~ " . $item["max_price"];
            switch($item['shop_type']) {
                case 1:
                    $item['shop_type_desc'] = '官方自营';
                    break;
                case 2:
                    $item['shop_type_desc'] = '入驻商家';
                    break;
            }
            $item['shop_logo'] = empty($item['shop_logo']) ? '' : UrlServer::getFileUrl($item['shop_logo']);

            if(!empty($item['column_ids'])) {
                $columnArr = explode(',', $item['column_ids']);
                $columnStr = '';
                foreach($columnArr as $cloumnId) {
                    $columnName = GoodsColumn::where('id', $cloumnId)->value('name');
                    $columnStr = $columnStr . $columnName . ',';
                }
                $columnStr = substr($columnStr, 0, strlen($columnStr) -1);
                $item['columnStr'] = $columnStr;
            }
        }
        if($count) {
            $lists = $lists->toArray();
        }else{
            $lists = [];
        }
        return ['count' => $count, 'lists' => $lists];
    }

    /**
     * 获取商品信息
     * @param $goods_id
     * @return array
     */
    public static function info($goods_id)
    {
        // 商品主表
        $info['base'] = Goods::where(['id' => $goods_id])
            ->withAttr('abs_image', function ($value, $data) {
                return UrlServer::getFileUrl($data['image']);
            })
            ->withAttr('content', function ($value){
                $preg = '/(<img .*?src=")[^https|^http](.*?)(".*?>)/is';
                $local_url = UrlServer::getFileUrl('/');
                return  preg_replace($preg, "\${1}$local_url\${2}\${3}",$value);
            })
            ->withAttr('poster', function ($value){
                return empty($value) ? '' : UrlServer::getFileUrl($value);
            })
            ->withAttr('abs_video',function ($value,$data){
                if($data['video']){
                    return UrlServer::getFileUrl($data['video']);
                }
                return '';
            })->append(['abs_image','abs_video'])->find();
        // 商品轮播图
        $info['base']['goods_image'] = GoodsImage::where(['goods_id' => $goods_id])
            ->withAttr('abs_image', function ($value, $data) {
                return UrlServer::getFileUrl($data['uri']);})
            ->append(['abs_image'])
            ->select();
        // 商品SKU
        $info['item'] =GoodsItem::where(['goods_id' => $goods_id])
            ->withAttr('abs_image', function ($value, $data) {
                return $data['image'] ? UrlServer::getFileUrl($data['image']) : '';
            })->append(['abs_image'])
            ->select();
        // 商品规格项
        $info['spec'] = GoodsSpec::where(['goods_id' => $goods_id])->select();
        // 商品规格值
        $spec_value = GoodsSpecValue::where(['goods_id' => $goods_id])->select();

        $data = [];
        foreach ($spec_value as $k => $v) {
            $data[$v['spec_id']][] = $v;
        }
        foreach ($info['spec'] as $k => $v) {
            $info['spec'][$k]['values'] = isset($data[$v['id']]) ? $data[$v['id']] : [];
        }
        return $info;
    }

    /**
     * 违规重审
     * @param $params
     */
    public static function reAudit($params)
    {
        Db::startTrans();
        try{
            // 更新商品信息
            $updateData = [
                'id' => $params['goods_id'],
                'audit_remark' => trim($params['reason']),
                'audit_status' => GoodsEnum::AUDIT_STATUS_REFUSE
            ];
            Goods::update($updateData);
            // 对应的秒杀商品同步更新为待审核
            SeckillGoods::where([
                'del' => 0,
                'goods_id' => $params['goods_id']
            ])->update([
                'review_status' => 0,
                'update_time' => time()
            ]);

            event('UpdateCollect', ['goods_id' => $params['goods_id']]);

            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 商品设置
     */
    public static function setInfo($params)
    {
        $updateData = [
            'id' => $params['goods_id'],
            'sales_virtual' => $params['sales_virtual'],
            'clicks_virtual' => $params['clicks_virtual'],
            'sort_weight' => $params['sort_weight'],
            'column_ids' => $params['select']
        ];
        return Goods::update($updateData);
    }

    /**
     * 审核
     */
    public static function audit($params)
    {
        $updateData = [
            'id' => $params['goods_id'],
            'audit_status' => $params['audit_status'],
            'audit_remark' => $params['audit_remark'],
        ];
        return Goods::update($updateData);
    }


    /**
     * @notes 批量下架
     * @param $params
     * @return bool
     * @author ljj
     * @date 2022/9/20 6:17 下午
     */
    public static function moreLower($params)
    {
        Db::startTrans();
        try{
            $ids = explode(',',$params['ids']);
            foreach ($ids as $id) {
                // 更新商品信息
                $updateData = [
                    'id' => $id,
                    'audit_remark' => trim($params['reason']),
                    'audit_status' => GoodsEnum::AUDIT_STATUS_REFUSE
                ];
                Goods::update($updateData);
                // 对应的秒杀商品同步更新为待审核
                SeckillGoods::where([
                    'del' => 0,
                    'goods_id' => $id
                ])->update([
                    'review_status' => 0,
                    'update_time' => time()
                ]);

                event('UpdateCollect', ['goods_id' => $id]);
            }

            Db::commit();
            return true;
        }catch(\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 批量审核
     * @param $params
     * @return bool
     * @author ljj
     * @date 2022/9/20 6:36 下午
     */
    public static function moreAudit($params)
    {
        $ids = explode(',',$params['ids']);
        foreach ($ids as $id) {
            $updateData = [
                'id' => $id,
                'audit_status' => $params['audit_status'],
                'audit_remark' => $params['audit_remark'],
            ];
            Goods::update($updateData);
        }
        return true;
    }
}
