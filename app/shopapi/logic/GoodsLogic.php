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

namespace app\shopapi\logic;

use app\common\basics\Logic;
use app\common\enum\GoodsEnum;
use app\common\enum\ShopEnum;
use app\common\model\goods\Goods;
use app\common\model\goods\GoodsItem;
use app\common\model\goods\GoodsSpec;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\facade\Db;

class GoodsLogic extends Logic
{
    /**
     * @notes 商品列表
     * @param $params
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/11/10 11:16
     */
    public function lists($params)
    {
        // 组装条件
        $field = $this->assemblyField();
        $where = $this->assemblyWhere($params);
        $order = $this->assemblyOrder();

        $lists = Goods::field($field)
            ->where($where)
            ->page($params['page_no'], $params['page_size'])
            ->order($order)
            ->select()
            ->toArray();

        $count = Goods::where($where)->count();

        $lists = $this->formatLists($lists);

        $more = is_more($count, $params['page_no'], $params['page_size']);
        $btns = $this->btns($params);
        $data = [
            'lists'          => $lists,
            'page_no'       => $params['page_no'],
            'page_size'     => $params['page_size'],
            'count'         => $count,
            'more'          => $more,
            'btns'          => $btns,
        ];

        return $data;
    }

    /**
     * @notes 字段
     * @return string[]
     * @author Tab
     * @date 2021/11/10 11:04
     */
    public function assemblyField()
    {
        return [
            "id",
            "name",
            "image",
            "min_price",
            "max_price",
            "stock",
            "sales_actual",
        ];
    }

    /**
     * @notes 搜索条件
     * @param $params
     * @return array[]
     * @author Tab
     * @date 2021/11/10 10:47
     */
    public function assemblyWhere($params)
    {
        // 商家
        $where = [
            ['shop_id', '=', $params['shop_id']]
        ];
        // 商品名称
        if(isset($params['name']) && trim($params['name'])) {
            $where[] = ['name','like','%'.trim($params['name']).'%'];
        }
        // 商品类型 - 默认销售中
        $type = !empty($params['type']) ? (int)$params['type'] : GoodsEnum::SALES;

        switch ($type) {
            case GoodsEnum::SALES:
                $where[] = ['status', '=', GoodsEnum::STATUS_SHELVES];//上架
                $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                $where[] = ['stock','exp', Db::raw('>stock_warn')];
                break;
            case GoodsEnum::WAREHOUSE:
                $where[] = ['status', '=', GoodsEnum::STATUS_SOLD_OUT];//下架
                $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                break;
            case GoodsEnum::WARNING:
                $where[] = ['status', '=', GoodsEnum::STATUS_SHELVES];//上架
                $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                $where[] = ['stock','exp', Db::raw('<=stock_warn')];
                break;
            case GoodsEnum::RECYCLE_BIN:
                $where[] = ['del', '=', GoodsEnum::DEL_RECYCLE];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_OK];//审核通过
                break;
            case GoodsEnum::WAIT_AUDIT:
                $where[] = ['del', '<>', GoodsEnum::DEL_TRUE];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_STAY];
                break;
            case GoodsEnum::UNPASS_AUDIT:
                $where[] = ['del', '<>', GoodsEnum::DEL_TRUE];
                $where[] = ['audit_status', '=', GoodsEnum::AUDIT_STATUS_REFUSE];
                break;
            default:
                $where[] = ['del', '=', GoodsEnum::DEL_NORMAL];
        }

        return $where;
    }

    /**
     * @notes 排序
     * @return string[]
     * @author Tab
     * @date 2021/11/10 10:47
     */
    public function assemblyOrder()
    {
        return  [
            'sort' => 'asc',
            'id' => 'desc'
        ];
    }

    /**
     * @notes 格式化
     * @param $lists
     * @return mixed
     * @author Tab
     * @date 2021/11/10 11:06
     */
    public function formatLists($lists)
    {
        if (empty($lists)) {
            return $lists;
        }

        foreach ($lists as &$item) {
            $minPrice = floor($item["min_price"] * 100);
            $maxPrice = floor($item["max_price"] * 100);
            $item['price'] = $minPrice == $maxPrice ? "¥" . clearZero($item["min_price"]) : "¥" . clearZero($item["min_price"]) . " ~ " . clearZero($item["max_price"]);
        }

        return $lists;
    }

    /**
     * @notes 操作
     * @param $shopId
     * @param $params
     * @return bool
     * @author Tab
     * @date 2021/11/10 14:08
     */
    public function operation($shopId, $params)
    {
        try {
            if (empty($params['action'])) {
                throw new \Exception("请选择操作");
            }

            $goods = Goods::where([
                'shop_id' => $shopId,
                'id' => $params['id']
            ])->findOrEmpty();
            if ($goods->isEmpty()) {
                throw new \Exception("商品不存在");
            }

            switch ($params['action']) {
                case "delete":
                    $this->delete($goods);
                    break;
                case "recycle":
                    $this->recycle($goods);
                    break;
                case "on_shelf":
                    $this->onShelf($goods);
                    break;
                case "off_shelf":
                    $this->offShelf($goods);
                    break;
                case "warehouse":
                    $this->warehouse($goods);
                    break;
                default:
                    throw new \Exception("无效的操作");
            }
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 删除商品
     * @param $goods
     * @author Tab
     * @date 2021/11/10 11:58
     */
    public function delete($goods)
    {
        $goods->del = GoodsEnum::DEL_TRUE;
        $goods->save();
    }

    /**
     * @notes 放入回收站
     * @param $goods
     * @author Tab
     * @date 2021/11/10 11:58
     */
    public function recycle($goods)
    {
        $goods->del = GoodsEnum::DEL_RECYCLE;
        $goods->save();
    }

    /**
     * @notes 上架
     * @param $goods
     * @throws \Exception
     * @author Tab
     * @date 2021/11/10 12:01
     */
    public function onShelf($goods)
    {
        if ($goods->stock <= 0) {
            throw new \Exception("库存不足不允许上架");
        }
        $goods->status = GoodsEnum::STATUS_SHELVES;
        $goods->save();
    }

    /**
     * @notes 下架
     * @param $goods
     * @author Tab
     * @date 2021/11/10 14:04
     */
    public function offShelf($goods)
    {
        $goods->status = GoodsEnum::STATUS_SOLD_OUT;
        $goods->save();
    }

    /**
     * @notes 放入仓库
     * @param $goods
     * @author Tab
     * @date 2021/11/10 14:07
     */
    public function warehouse($goods)
    {
        $goods->status = GoodsEnum::STATUS_SOLD_OUT;
        $goods->del = GoodsEnum::DEL_NORMAL;
        $goods->save();
    }

    /**
     * @notes 商品详情
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author Tab
     * @date 2021/11/10 15:14
     */
    public function detail($id)
    {
        $goodsDetail = Goods::with(['goods_image', 'goods_item', 'shop'])
            ->field('id,type,code,name,spec_type,image,video,remark,content,market_price,min_price,max_price,stock,sales_actual,shop_id')
            ->where('id', $id)
            ->findOrEmpty();

        if ($goodsDetail->isEmpty()) {
            return [];
        }

        $goodsDetail = $goodsDetail->toArray();

        // 轮播图添加域名
        foreach($goodsDetail['goods_image'] as &$item) {
            $item['uri'] = empty($item['uri']) ? '' : UrlServer::getFileUrl($item['uri']);
        }

        // 规格项及规格值信息
        $goodsDetail['goods_spec'] = GoodsSpec::with('spec_value')
            ->where('goods_id', $goodsDetail['id'])->select()->toArray();

        return $goodsDetail;
    }

    /**
     * @notes 商品编辑
     * @param $shopId
     * @param $params
     * @author Tab
     * @date 2021/11/10 15:24
     */
    public function edit($params)
    {
        try {
            $updateData = $this->checkParams($params);
            (new GoodsItem())->saveAll($updateData['itemData']);
            Goods::update($updateData['goodsData']);
            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * @notes 参数校验
     * @param $params
     * @throws \Exception
     * @author Tab
     * @date 2021/11/10 15:48
     */
    public function checkParams($params)
    {
        $goodsStock = 0;
        $itemId = 0;
        $updateData = [];

        $max_price = 0;
        $min_price = 0;
        $market_price = 0;

        if (!isset($params['items']) || !is_array($params['items'])) {
            throw new \Exception("参数缺失或格式有误");
        }
        foreach($params['items'] as $item) {
            if (!is_array($item)) {
                throw new \Exception("参数格式错误");
            }
            if (!isset($item['id']) || !isset($item['price']) || !isset($item['stock']) || !isset($item['market_price']) || !isset($item['chengben_price'])) {
                throw new \Exception("参数缺失");
            }
            if ($item['price'] <= 0 || $item['market_price'] <= 0 || $item['chengben_price'] <= 0 || $item['stock'] <= 0) {
                throw new \Exception("价格及库存均不能为负数和零");
            }
            if ($item['market_price'] < $item['price']) {
                throw new \Exception("市场价不能低于售价");
            }
            // 重新赋值作用：避免$item字段过多时修改了不该修改的字段
            $temp['id'] = $item['id'];
            $temp['price'] = $item['price'];
            $temp['market_price'] = $item['market_price'];
            $temp['chengben_price'] = $item['chengben_price'];
            $temp['stock'] = $item['stock'];
            // 规格数据
            $updateData[] = $temp;

            // 主表库存
            $goodsStock += $item['stock'];
            $itemId = $item['id'];

            // 最高价
            if ($item['price'] > $max_price) {
                $max_price = $item['price'];
            }

            if ($min_price == 0) {
                $min_price = $item['price'];
                $market_price = $item['market_price'];
            }

            //最低价
            if ($item['price'] < $min_price) {
                $min_price = $item['price'];
                $market_price = $item['market_price'];
            }
        }

        // 商品id
        $goodsId = (new GoodsItem())->where(['id' => $itemId])->value('goods_id');

        // 商品更新库存
        $goodsData = [
            'id' => $goodsId,
            'stock' => $goodsStock,
            'max_price' => $max_price,
            'min_price' => $min_price,
            'market_price' => $market_price,
        ];

        return ['itemData' => $updateData, 'goodsData' => $goodsData];
    }

    /**
     * @notes 按钮显示与隐藏
     * @param $params
     * @return array
     * @author Tab
     * @date 2021/11/12 17:59
     */
    public function btns($params)
    {
        $recycleBtn = $editBtn = $offShelfBtn = $onShelfBtn = $deleteBtn = $warehouseBtn = false;
        // 商品类型 - 默认销售中
        $type = !empty($params['type']) ? (int)$params['type'] : GoodsEnum::SALES;

        switch ($type) {
            case GoodsEnum::SALES:
            case GoodsEnum::WARNING:
                $recycleBtn = $editBtn = $offShelfBtn = true;
                break;
            case GoodsEnum::WAREHOUSE:
                $recycleBtn = $editBtn = $onShelfBtn = true;
                break;
            case GoodsEnum::RECYCLE_BIN:
                $deleteBtn = $warehouseBtn = true;
                break;
            case GoodsEnum::WAIT_AUDIT:
            case GoodsEnum::UNPASS_AUDIT:
                $deleteBtn = $editBtn = true;
                break;
        }
        return [
            "recycle_btn" => $recycleBtn,
            "edit_btn" => $editBtn,
            "off_shelf_btn" => $offShelfBtn,
            "on_shelf_btn" => $onShelfBtn,
            "delete_btn" => $deleteBtn,
            "warehouse_btn" => $warehouseBtn,
        ];
    }
}