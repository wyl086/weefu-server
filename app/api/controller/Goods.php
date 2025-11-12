<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\GoodsLogic;
use app\common\server\JsonServer;
use think\facade\Validate;


class Goods extends Api
{
    public $like_not_need_login = ['getGoodsDetail', 'getHotList', 'getGoodsList', 'getGoodsListTemplate', 'getGoodsListByColumnId'];

    /**
     * 商品详情
     */
    public function getGoodsDetail()
    {
        if($this->request->isGet()) {
            $goodsId = $this->request->get('goods_id', '', 'trim');
            $validate = Validate::rule('goods_id', 'require|integer|gt:0');
            if(!$validate->check(['goods_id'=>$goodsId])) {
                return JsonServer::error($validate->getError());
            }
            $goodsDetail = GoodsLogic::getGoodsDetail($goodsId, $this->user_id);
            if(false === $goodsDetail) {
                $error = GoodsLogic::getError() ?? '获取商品详情失败';
                return JsonServer::error($error);
            }
            return JsonServer::success('获取商品详情成功', $goodsDetail);
        }else{
            return JsonServer::error('请求方式错误');
        }
    }

    /**
     * 热销榜单
     */
    public function getHotList()
    {
        return $this->getGoodsListTemplate($this->request, 'getHotList');
    }

    /**
     * 商品列表
     */
    public function getGoodsList(){
        return $this->getGoodsListTemplate($this->request, 'getGoodsList');
    }

    /**
     * 商品列表模板
     * 作用：代码复用
     */
    public function getGoodsListTemplate($request, $methodName)
    {
        if($request->isGet()) {
            $get = $this->request->get();
            $get['user_id'] = $this->user_id;
            $get['page_no'] = $this->page_no;
            $get['page_size'] = $this->page_size;
            $data = GoodsLogic::$methodName($get); // 可变方法
            return JsonServer::success('获取成功', $data);
        }else{
            return JsonServer::error('请求方式错误');
        }
    }

    /**
     * 根据商品栏目获取商品列表
     */
    public function getGoodsListByColumnId()
    {
        if($this->request->isGet()) {
            $columnId = $this->request->get('column_id', '', 'trim');
            $validate = Validate::rule('column_id', 'require|integer|gt:0');
            if(!$validate->check(['column_id'=>$columnId])) {
                return JsonServer::error($validate->getError());
            }
            $data = GoodsLogic::getGoodsListByColumnId($columnId,$this->page_no, $this->page_size);
            return JsonServer::success('获取成功', $data);
        }else{
            return JsonServer::error('请求方式错误');
        }
    }
}