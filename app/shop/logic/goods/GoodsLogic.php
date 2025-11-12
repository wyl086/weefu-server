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
use app\common\enum\GoodsEnum;
use app\common\model\distribution\DistributionGoods;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsImage;
use app\common\model\goods\GoodsItem;
use app\common\model\goods\GoodsSpec;
use app\common\model\goods\GoodsSpecValue;
use app\common\server\JsonServer;
use app\common\server\UrlServer;
use think\facade\Db;
use app\common\model\shop\Shop;
use think\facade\Validate;


/**
 * 商品管理-逻辑
 * Class GoodsLogic
 * @package app\shop\logic\goods
 */
class GoodsLogic extends Logic
{
    /**
     * @notes 商品统计
     * @param $shop_id
     * @return int[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/7 11:57
     */
    public static function statistics($shop_id){
        $where = [
            ['del', '<>', GoodsEnum::DEL_TRUE],
            ['shop_id', '=', $shop_id]
        ];

        return [
            // 销售中商品
            // 销售状态：上架中；删除状态：正常； 审核状态： 审核通过
            'sell'      => Goods::where($where)
                ->where('del', GoodsEnum::DEL_NORMAL)
                ->where('status', GoodsEnum::STATUS_SHELVES)
                ->where('audit_status', GoodsEnum::AUDIT_STATUS_OK)
                ->where('stock', '>=', Db::raw('stock_warn'))
                ->count(),
            // 库存预警商品
            // 销售状态：上架中；删除状态：正常； 审核状态： 审核通过；总库存 < 库存预警
            'warn'      => Goods::where($where)
                ->where('del', GoodsEnum::DEL_NORMAL)
                ->where('status', GoodsEnum::STATUS_SHELVES)
                ->where('audit_status', GoodsEnum::AUDIT_STATUS_OK)
                ->where('stock', '<', Db::raw('stock_warn'))
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
        $where = [
            ['shop_id', '=', $get['shop_id']]
        ];
        if(isset($get['goods_name']) && $get['goods_name']) {
            $where[] = ['name','like','%'.$get['goods_name'].'%'];
        }
        if(!empty($get['platform_cate_id'])) {
            $where[] = ['first_cate_id|second_cate_id|third_cate_id','=', $get['platform_cate_id']];
        }

        if(!empty($get['shop_cate_id'])) {
            $where[] = ['shop_cate_id','=', $get['shop_cate_id']];
        }

        if(isset($get['goods_type']) && $get['goods_type'] != '') {
            $where[] = ['type','=', $get['goods_type']];
        }
        
        if (Validate::must($get['is_distribution'] ?? '')) {
            $where[] = [ 'is_distribution', '=', $get['is_distribution'] ];
        }
        
        if (Validate::must($get['is_member'] ?? '')) {
            $where[] = [ 'is_member', '=', $get['is_member'] ];
        }

        $type = $get['type'] ?? 0;

        switch ($type) {
            case 1:     //销售中
                $where[] = ['status', '=', GoodsEnum::STATUS_SHELVES];//上架
                $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                $where[] = ['stock','exp', Db::raw('>=stock_warn')];
                break;
            case 2:     //库存预警
                $where[] = ['status', '=', GoodsEnum::STATUS_SHELVES];//上架
                $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                $where[] = ['stock','exp', Db::raw('<stock_warn')];
                break;
            case 3:      //仓库中
                $where[] = ['status', '=', GoodsEnum::STATUS_SOLD_OUT];//下架
                $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                break;
            case 4:     //回收站
                $where[] = ['del', '=', GoodsEnum::DEL_RECYCLE];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                break;
            case 5:  //待审核
                $where[] = ['del', '<>', GoodsEnum::DEL_TRUE];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_STAY];
                break;
            case 6: //审核未通过
                $where[] = ['del', '<>', GoodsEnum::DEL_TRUE];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_REFUSE];
                break;
            default:
                $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
        }

        $order = [
            'sort' => 'asc',
            'id' => 'desc'
        ];

        $lists = Goods::where($where)
            ->append([ 'is_distribution_desc', 'is_member_desc' ])
            ->page($get['page'], $get['limit'])
            ->order($order)
            ->select();
        $count = Goods::where($where)->count();
        if($count) {
            $lists = $lists->toArray();
        }else{
            $lists = [];
        }
        foreach ($lists as &$item) {
            // 处理价格格式
            $item['price'] = $item['spec_type'] == 1 ? $item["min_price"] : $item["min_price"] . " ~ " . $item["max_price"];
        }
        return ['count' => $count, 'lists' => $lists];
    }


    /**
     * Notes: 添加商品
     * @param $shop_id
     * @param $post
     * @param $spec_lists
     * @author 段誉(2021/4/20 15:14)
     * @return bool
     */
    public static function add($shop_id, $post, $spec_lists)
    {
        Db::startTrans();
        try {
            // 图片去除域名
            $post['image'] = UrlServer::setFileUrl($post['image']);
            $post['goods_image'] = array_map(function($value) {
                return UrlServer::setFileUrl($value);
            }, $post['goods_image']);

            //添加商品主表
            $goods_id = self::addGoods($shop_id, $post);

            //添加商品轮播图
            self::addGoodsImage($goods_id,$post);

            //添加规格项、规格值、SKU
            if ($post['spec_type'] == 1) {
                self::addOneSpec($goods_id, $post);
            } else {
                self::addMoreSpec($goods_id, $post, $spec_lists);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 删除
     * @param $shop_id
     * @param $id
     * @return Goods
     * @author 段誉
     * @date 2022/2/14 18:51
     */
    public static function del($shop_id, $id)
    {
        $result = Goods::update(['del' => 1], ['id' => $id, 'shop_id' => $shop_id]);
        event('UpdateCollect', ['goods_id' => $id]);
        return $result;
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
     * 编辑
     */
    public static function edit($post, $spec_lists)
    {
        Db::startTrans();
        try {
    
            $oldItemIds = GoodsItem::where('goods_id', $post['id'])->column('id');
            
            //计算最大最小价格
            if ($post['spec_type'] == 1) {
                $max_price = $post['one_price'];
                $min_price = $post['one_price'];
                $market_price = $post['one_market_price'];
                $total_stock = $post['one_stock'];
            } else {
                $max_price = max($post['price']);
                $min_price = min($post['price']);
                $min_price_key = array_search($min_price,$post['price']);
                $market_price = $post['market_price'][$min_price_key];
                $total_stock = array_sum($post['stock']);
            }

            // 库存校验
            if($post['status'] == GoodsEnum::STATUS_SHELVES && $total_stock == 0) {
                throw new \Exception('库存为0不允许上架');
            }

            $old_spec_type = Goods::where('id', $post['goods_id'])->value('spec_type');

            // 格式化数据
            $post = self::formatGoodsData($post);

            //更新主表
            $data = [
                'name'                      => $post['name'],
                'code'                      => $post['code'],
                'shop_cate_id'              => $post['shop_cate_id'],
                'first_cate_id'             => $post['first_cate_id'],
                'second_cate_id'            => $post['second_cate_id'],
                'third_cate_id'             => $post['third_cate_id'],
                'brand_id'                  => $post['brand_id'],
                'unit_id'                   => $post['unit_id'],
                'supplier_id'               => $post['supplier_id'],
                'status'                    => $post['status'],
                'image'                     => clearDomain($post['image']),
                'video'                     => $post['video'] ?? '',
                'remark'                    => $post['remark'],
                'content'                   => $post['content'],
                'sort'                      => $post['sort'],
                'spec_type'                 => $post['spec_type'],
                'max_price'                 => $max_price,
                'min_price'                 => $min_price,
                'market_price'              => $market_price,
                'stock'                     => $total_stock,
                'express_type'              => $post['express_type'],
                'express_money'             => $post['express_money'],
                'express_template_id'       => $post['express_template_id'],
                'is_recommend'              => $post['is_recommend'],
                'update_time'               => time(),
                'stock_warn'                => $post['stock_warn'],
                'poster'                    => isset($post['poster']) ? clearDomain($post['poster']) : '',
                'is_show_stock'             => $post['is_show_stock'],
                'is_member'                 => $post['is_member'],
                'delivery_type'             => implode(',', $post['delivery_type']),
                'after_pay'                 => $post['after_pay'] ?? 0,
                'after_delivery'            => $post['after_delivery'] ?? 0,
                'delivery_content'          => $post['delivery_content'] ?? '',
            ];

            // 判断是否为未审核通过的商品
            $audit_status = Goods::where(['id' => $post['goods_id']])->value('audit_status');
            if($audit_status == GoodsEnum::AUDIT_STATUS_REFUSE) {
                $data['audit_status'] = GoodsEnum::AUDIT_STATUS_STAY; // 编辑后未审核通过的商品状态置为待审核
            }

            Goods::where(['id' => $post['goods_id']])->update($data);

            if ($data['status'] != GoodsEnum::STATUS_SHELVES) {
                event('UpdateCollect', ['goods_id' => $post['goods_id']]);
            }

            //先删除再重新添加轮播图
            GoodsImage::where(['goods_id' => $post['goods_id']])->delete();
            $data = [];
            foreach ($post['goods_image'] as $k => $v) {
                $data[] = [
                    'goods_id' => $post['goods_id'],
                    'uri' => clearDomain($v),
                ];
            }
            (new GoodsImage())->saveAll($data);

            //写入规格表
            if ($post['spec_type'] == 1) {
                //单规格写入
                if ($old_spec_type == 1) {
                    //原来是单规格
                    $data = [
                        'image'             => isset($post['one_spec_image']) ? clearDomain($post['one_spec_image']) : '',
                        'market_price'      => $post['one_market_price'],
                        'price'             => $post['one_price'],
                        'chengben_price'    => $post['one_chengben_price'],
                        'stock'             => $post['one_stock'],
                        'weight'            => $post['one_weight'],
                        'volume'            => $post['one_volume'],
                        'bar_code'          => $post['one_bar_code'],
                    ];
                    GoodsItem::where(['goods_id' => $post['goods_id']])->update($data);
                } else {
                    //原来多规格
                    //删除多规格
                    GoodsSpec::where('goods_id', $post['goods_id'])->delete();
                    GoodsSpecValue::where('goods_id', $post['goods_id'])->delete();
                    GoodsItem::where('goods_id', $post['goods_id'])->delete();
                    $goodsSpec = GoodsSpec::create(['goods_id' => $post['goods_id'], 'name' => '默认']);
                    $goods_spec_id = $goodsSpec->id;
                    $goodsSpecValue = GoodsSpecValue::create(['spec_id' => $goods_spec_id, 'goods_id' => $post['goods_id'], 'value' => '默认']);
                    $goods_spec_value_id = $goodsSpecValue->id;
                    $data = [
                        'image'             => isset($post['one_spec_image']) ? clearDomain($post['one_spec_image']) : '',
                        'goods_id'          => $post['goods_id'],
                        'spec_value_ids'    => $goods_spec_value_id,
                        'spec_value_str'    => '默认',
                        'market_price'      => $post['one_market_price'],
                        'price'             => $post['one_price'],
                        'chengben_price'    => $post['one_chengben_price'],
                        'stock'             => $post['one_stock'],
                        'volume'            => $post['one_volume'],
                        'weight'            => $post['one_weight'],
                        'bar_code'          => $post['one_bar_code'],
                    ];
                    GoodsItem::create($data);
                }
            } else {
                // 多规格写入
                $goods_specs = [];
                foreach ($post['spec_name'] as $k => $v) {
                    $temp = ['goods_id' => $post['goods_id'], 'name' => $v, 'spec_id' => $post['spec_id'][$k]];
                    $goods_specs[] = $temp;
                }
                $new_spec_name_ids = [];
                foreach ($goods_specs as $k => $v) {
                    if ($v['spec_id']) {
                        //更新规格名
                        GoodsSpec::where(['goods_id' => $post['goods_id'], 'id' => $v['spec_id']])
                            ->update(['name' => $v['name']]);
                        $new_spec_name_ids[] = $v['spec_id'];
                    } else {
                        //添加规格名
                        $goodsSpec = GoodsSpec::create(['goods_id' => $post['goods_id'], 'name' => $v['name']]);
                        $new_spec_name_ids[] = $goodsSpec->id;
                    }
                }
                //删除规格项
                $all_spec_ids = GoodsSpec::where('goods_id', $post['goods_id'])->column('id');
                $del_spec_name_ids = array_diff($all_spec_ids, $new_spec_name_ids);
                if (!empty($del_spec_name_ids)) {
                    GoodsSpec::where('goods_id', $post['goods_id'])
                        ->where('id', 'in', $del_spec_name_ids)
                        ->delete();
                }

                $new_spec_value_ids = [];
                $goods_spec_name_key_id = Db::name('goods_spec')
                    ->where(['goods_id' => $post['goods_id']])
                    ->where('name', 'in', $post['spec_name'])
                    ->column('id', 'name');
                foreach ($post['spec_values'] as $k => $v) {
                    $value_id_row = explode(',', $post['spec_value_ids'][$k]);
                    $value_row = explode(',', $v);
                    foreach ($value_row as $k2 => $v2) {
                        $temp = [
                            'goods_id' => $post['goods_id'],
                            'spec_id' => $goods_spec_name_key_id[$post['spec_name'][$k]],
                            'value' => $v2,
                        ];
                        if ($value_id_row[$k2]) {
                            //更新规格值
                            Db::name('goods_spec_value')
                                ->where(['id' => $value_id_row[$k2]])
                                ->update($temp);
                            $new_spec_value_ids[] = $value_id_row[$k2];
                        } else {
                            //添加规格值
                            $new_spec_value_ids[] = Db::name('goods_spec_value')
                                ->insertGetId($temp);
                        }
                    }
                }
                $all_spec_value_ids = Db::name('goods_spec_value')
                    ->where('goods_id', $post['goods_id'])
                    ->column('id');
                $del_spec_value_ids = array_diff($all_spec_value_ids, $new_spec_value_ids);
                if (!empty($del_spec_value_ids)) {
                    //删除规格值
                    Db::name('goods_spec_value')
                        ->where('goods_id', $post['goods_id'])
                        ->where('id', 'in', $del_spec_value_ids)
                        ->delete();
                }

                $new_item_id = [];
                $goods_spec_name_value_id = Db::name('goods_spec_value')
                    ->where(['goods_id' => $post['goods_id']])
                    ->column('id', 'value');
                foreach ($spec_lists as $k => $v) {
                    $spec_lists[$k]['spec_value_ids'] = '';
                    $temp = explode(',', $v['spec_value_str']);
                    foreach ($temp as $k2 => $v2) {
                        $spec_lists[$k]['spec_value_ids'] .= $goods_spec_name_value_id[$v2] . ',';
                    }
                    $spec_lists[$k]['spec_value_ids'] = trim($spec_lists[$k]['spec_value_ids'], ',');
                    if(isset($spec_lists[$k]['spec_image'])) {
                        $spec_lists[$k]['image'] = clearDomain($spec_lists[$k]['spec_image']);
                    }

                    unset($spec_lists[$k]['spec_image']);
                    $spec_lists[$k]['goods_id'] = $post['goods_id'];
                    unset($spec_lists[$k]['spec_id']);
                    $item_id = $spec_lists[$k]['item_id'];
                    unset($spec_lists[$k]['item_id']);
                    if ($item_id) {
                        Db::name('goods_item')
                            ->where(['id' => $item_id])
                            ->update($spec_lists[$k]);
                        $new_item_id[] = $item_id;
                    } else {
                        $new_item_id[] = Db::name('goods_item')
                            ->insertGetId($spec_lists[$k]);
                    }
                }
                $all_item_id = Db::name('goods_item')
                    ->where('goods_id', $post['goods_id'])
                    ->column('id');
                $del_item_ids = array_diff($all_item_id, $new_item_id);
                if (!empty($del_item_ids)) {
                    //删除规格值
                    Db::name('goods_item')
                        ->where('goods_id', $post['goods_id'])
                        ->where('id', 'in', $del_item_ids)
                        ->delete();
                }
            }
    
            $newItemIds = GoodsItem::where('goods_id', $post['id'])->column('id');
            $destroyIds = DistributionGoods::where('goods_id', $post['goods_id'])->column('id');
    
            // 删除原来的分销设置
            if ($oldItemIds != $newItemIds && $destroyIds) {
                DistributionGoods::destroy($destroyIds);
                self::$error = '商品信息修改成功,该商品属于分销商品，请重新设置分销信息';
            }
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * 放回仓库
     */
    public static function backToWarehouse($id)
    {
        $updateData = [
            'id' => $id,
            'status' => GoodsEnum::STATUS_SOLD_OUT,
            'del' => GoodsEnum::DEL_NORMAL
        ];
        return Goods::update($updateData);
    }


    /**
     * @notes 批量更新商品状态
     * @param $ids
     * @param $status
     * @return Goods|false
     * @author 段誉
     * @date 2022/3/17 11:51
     */
    public static function setStatus($ids, $status)
    {
        try {
            $result = Goods::whereIn('id', $ids)->update([
                'status'      => $status,
                'update_time' => time()
            ]);

            if (!$status) {
                // 下架商品，更新商品收藏
                event('UpdateCollect', ['goods_id' => $ids]);
            }

            return $result;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 添加基础商品信息
     * @param $shop_id
     * @param $post
     * @return mixed
     * @throws \Exception
     * @author 段誉
     * @date 2022/4/7 11:55
     */
    public static function addGoods($shop_id, $post)
    {
        //算出最大最小价格
        if ($post['spec_type'] == 1) {
            $max_price = $post['one_price'];
            $min_price = $post['one_price'];
            $market_price = $post['one_market_price'];
            $total_stock = $post['one_stock'];
        } else { // 多规格
            $max_price = max($post['price']);
            $min_price = min($post['price']);
            $min_price_key = array_search($min_price,$post['price']);
            $market_price = $post['market_price'][$min_price_key];
            $total_stock = array_sum($post['stock']);
        }

        // 总库存为0的商品不允许上架
        if($post['status'] == GoodsEnum::STATUS_SHELVES && $total_stock == 0) {
            throw new \Exception('库存为0不允许上架');
        }

        // 判断商品是否需要审核
        $is_product_audit = Shop::where('id', $shop_id)->value('is_product_audit'); // 0-无需审核 1-需审核
        $audit_status = $is_product_audit ? 0 : 1;

        // 处理商品数据
        $post = self::formatGoodsData($post);

        //写入主表
        $data = [
            'type'                      => $post['type'],
            'name'                      => trim($post['name']),
            'code'                      => trim($post['code']) ? trim($post['code']) : create_goods_code($shop_id),
            'shop_id'                   => $shop_id,
            'shop_cate_id'              => $post['shop_cate_id'],
            'first_cate_id'             => $post['first_cate_id'],
            'second_cate_id'            => $post['second_cate_id'],
            'third_cate_id'             => $post['third_cate_id'],
            'unit_id'                   => $post['unit_id'],
            'brand_id'                  => $post['brand_id'],
            'supplier_id'               => $post['supplier_id'],
            'status'                    => $post['status'],
            'image'                     => $post['image'],
            'video'                     => $post['video'] ?? '',
            'remark'                    => $post['remark'],
            'content'                   => $post['content'],
            'sort'                      => $post['sort'],
            'spec_type'                 => $post['spec_type'],
            'max_price'                 => $max_price,
            'min_price'                 => $min_price,
            'market_price'              => $market_price,
            'stock'                     => $total_stock, // 总库存
            'express_type'              => $post['express_type'] ?? 0,
            'express_money'             => $post['express_money'],
            'express_template_id'       => $post['express_template_id'],
            'is_recommend'              => $post['is_recommend'],
            'create_time'               => time(),
            'update_time'               => time(),
            'stock_warn'                => $post['stock_warn'],
            'poster'                    => isset($post['poster']) ? UrlServer::setFileUrl($post['poster']) : '',
            'is_show_stock'             => $post['is_show_stock'],
            'is_member'                 => $post['is_member'],
            'audit_status'              => $audit_status,
            'delivery_type'             => implode(',', $post['delivery_type']),
            'after_pay'                 => $post['after_pay'] ?? 0,
            'after_delivery'            => $post['after_delivery'] ?? 0,
            'delivery_content'          => $post['delivery_content'] ?? '',
        ];
        $goods = Goods::create($data);
        return $goods->id;
    }


    /**
     * @notes 添加商品图片
     * @param $goods_id
     * @param $post
     * @throws \Exception
     * @author 段誉
     * @date 2022/4/7 11:56
     */
    public static function addGoodsImage($goods_id, $post)
    {
        $data = [];
        foreach ($post['goods_image'] as $k => $v) {
            $data[] = [
                'goods_id' => $goods_id,
                'uri' => $v,
            ];
        }
        (new GoodsImage())->saveAll($data);
    }


    /**
     * @notes 添加单个规格
     * @param $goods_id
     * @param $post
     * @author 段誉
     * @date 2022/4/7 11:56
     */
    public static function addOneSpec($goods_id, $post)
    {
        //添加商品规格
        $goods_spec_id = (new GoodsSpec())->insertGetId([
            'goods_id' => $goods_id,
            'name' => '默认'
        ]);

        //添加商品规格值
        $goods_spec_value_id = (new GoodsSpecValue())->insertGetId([
            'spec_id' => $goods_spec_id,
            'goods_id' => $goods_id,
            'value' => '默认'
        ]);

        if(isset($post['one_spec_image'])) {
            $post['one_spec_image'] = str_replace(request()->domain(), '',  $post['one_spec_image']);
        }
        //商品sku
        GoodsItem::create([
            'image'             => $post['one_spec_image'] ?? $post['image'],
            'goods_id'          => $goods_id,
            'spec_value_ids'    => $goods_spec_value_id,
            'spec_value_str'    => '默认',
            'market_price'      => $post['one_market_price'],
            'price'             => $post['one_price'],
            'stock'             => $post['one_stock'],
            'volume'            => $post['one_volume'],
            'weight'            => $post['one_weight'],
            'bar_code'          => $post['one_bar_code'],
            'chengben_price'    => $post['one_chengben_price'],
        ]);
    }


    /**
     * @notes 添加多个规格
     * @param $goods_id
     * @param $post
     * @param $spec_lists
     * @author 段誉
     * @date 2022/4/7 11:56
     */
    public static function addMoreSpec($goods_id, $post, $spec_lists)
    {
        // 添加规格项
        $goods_specs = [];
        foreach ($post['spec_name'] as $k => $v) {
            $temp = ['goods_id' => $goods_id, 'name' => $v];
            $goods_specs[] = $temp;
        }
        (new GoodsSpec())->insertAll($goods_specs);

        // 规格项id及名称 例：['颜色'=>1, '尺码'=>2]
        $goods_spec_name_key_id = GoodsSpec::where(['goods_id' => $goods_id])
            ->where('name', 'in', $post['spec_name'])
            ->column('id', 'name');

        // 添加规格值
        $data = [];
        foreach ($post['spec_values'] as $k => $v) {
            $row = explode(',', $v);
            foreach ($row as $k2 => $v2) {
                $temp = [
                    'goods_id' => $goods_id,
                    'spec_id' => $goods_spec_name_key_id[$post['spec_name'][$k]],
                    'value' => $v2,
                ];
                $data[] = $temp;
            }
        }
        (new GoodsSpecValue())->insertAll($data);

        // 规格值id及名称   例：['红色'=>1,'蓝色'=>2,'S码'=>3,'M码'=>4]
        $goods_spec_name_value_id = GoodsSpecValue::where(['goods_id' => $goods_id])->column('id', 'value');

        // 添加SKU
        foreach ($spec_lists as $k => $v) {
            $spec_lists[$k]['spec_value_ids'] = '';
            $temp = explode(',', $v['spec_value_str']); // 例："红色,S码" => ["红色", "S码"]

            // 组装SKU的spec_value_ids 例："红色,S码" => ["红色", "S码"] => "1,3"
            foreach ($temp as $k2 => $v2) {
                $spec_lists[$k]['spec_value_ids'] .= $goods_spec_name_value_id[$v2] . ',';
            }
            $spec_lists[$k]['spec_value_ids'] = trim($spec_lists[$k]['spec_value_ids'], ',');
            if(isset($spec_lists[$k]['spec_image'])) {
                $spec_lists[$k]['spec_image'] = str_replace(request()->domain(), '',  $spec_lists[$k]['spec_image']);
            }
            $spec_lists[$k]['image'] = $spec_lists[$k]['spec_image'] ?? $post['image'];
            $spec_lists[$k]['goods_id'] = $goods_id;
            if(isset($spec_lists[$k]['spec_image'])) {
                unset($spec_lists[$k]['spec_image']);
            }
            unset($spec_lists[$k]['spec_id']);
            unset($spec_lists[$k]['item_id']);
        }
        (new GoodsItem())->insertAll($spec_lists);
    }


    /**
     * @notes 格式化商品数据
     * @param array $data
     * @return array
     * @author 段誉
     * @date 2022/4/7 10:12
     */
    public static function formatGoodsData(array $data) : array
    {
        // 虚拟商品类型数据
        if ($data['type'] == GoodsEnum::TYPE_VIRTUAL) {
            $data['after_pay'] = !empty($data['after_pay']) ? $data['after_pay'] : 0;
            $data['after_delivery'] = !empty($data['after_delivery']) ? $data['after_delivery'] : 0;
            $data['delivery_content'] = !empty($data['delivery_content']) ? $data['delivery_content'] : '';
        }

        // 替换内容中图片地址
        $domain = UrlServer::getFileUrl('/');
        $data['content'] = str_replace($domain, '/', $data['content']);

        // 运费处理
        $data['express_money'] = $data['express_type'] == GoodsEnum::EXPRESS_TYPE_UNIFIED ? $data['express_money'] : 0;
        $data['express_template_id'] = $data['express_type'] == GoodsEnum::EXPRESS_TYPE_TEMPLATE ? $data['express_template_id'] : 0;

        return $data;
    }

}
