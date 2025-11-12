<?php
namespace app\api\controller;

use app\common\basics\Api;
use app\api\logic\IndexLogic;
use app\common\logic\ChatLogic;
use app\common\logic\RegionLogic;
use app\common\server\JsonServer;

class Index extends Api
{
    public $like_not_need_login = ['index', 'indexCategory', 'config','copyright','city','geocoder'];

    // 首页
    public function index()
    {
        $terminal = $this->request->get('terminal', 'nmp');
        $city = $this->request->get('city_id', '');
        $data = IndexLogic::index($this->user_id,$terminal,$city);
        return JsonServer::success('获取成功', $data);
    }

    // 首页分类
    public function indexCategory()
    {
        $platform_category_id = $this->request->get('platform_category_id', '', 'intval');
        if(empty($platform_category_id)) {
            return JsonServer::error('平台分类id不能为空');
        }
        $data = IndexLogic::indexCategory($platform_category_id);
        return JsonServer::success('获取成功', $data);
    }

    // 通用配置
    public function config()
    {
        $data = IndexLogic::config();
        return JsonServer::success('获取成功', $data);
    }


    /**
     * @notes 客服配置
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/12/15 17:16
     */
    public function chatConfig()
    {
        $shop_id = $this->request->get('shop_id/d');
        $result = ChatLogic::getConfig($shop_id);
        return JsonServer::success($result['msg'], [], $result['code']);
    }

    /**
     * @notes 版权资质
     * @return \think\response\Json
     * @author ljj
     * @date 2022/2/22 10:16 上午
     */
    public function copyright()
    {
        $shop_id = $this->request->get('shop_id/d');
        $result = IndexLogic::copyright($shop_id);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 地级市
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author ljj
     * @date 2022/9/20 5:51 下午
     */
    public function city()
    {
        $result = RegionLogic::city();
        return JsonServer::success('', $result);
    }

    /**
     * @notes 腾讯地图逆地址解析(坐标位置描述)
     * @return \think\response\Json
     * @author ljj
     * @date 2022/9/21 2:37 下午
     * 经纬度到文字地址及相关位置信息的转换
     */
    public function geocoder()
    {
        $get = $this->request->get();
        if (!isset($get['location']) || $get['location'] == '') {
            return JsonServer::error('经纬度缺失');
        }

        $result = IndexLogic::geocoder($get);
        if ($result['status'] !== 0) {
            return JsonServer::error($result['message']);
        }
        return JsonServer::success('',$result);
    }
}