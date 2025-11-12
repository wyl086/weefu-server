<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\common\server\JsonServer;
use app\api\logic\GoodsCategoryLogic;
use think\facade\Validate;

class GoodsCategory extends Api
{
    public $like_not_need_login = ['getLevelOneList', 'getListByLevelOne'];

    /**
     * 获取一级分类列表
     */
    public function getLevelOneList()
    {
        if($this->request->isGet()) {
            $list = GoodsCategoryLogic::getLevelOneList();
            return JsonServer::success('获取成功', $list);
        }
        return JsonServer::error('请求方式错误');
    }

    /**
     * 获取一级分类下的后代分类
     */
    public function getListByLevelOne()
    {
        if($this->request->isGet()) {
            $id = $this->request->get('id', '', 'trim');
            $validate = Validate::rule('id', 'require|integer|gt:0');
            if(!$validate->check(['id'=>$id])) {
                return JsonServer::error($validate->getError());
            }
            $list = GoodsCategoryLogic::getListByLevelOne($id);
            return JsonServer::success('获取成功', $list);
        }
        return JsonServer::error('请求方式错误');
    }
}
