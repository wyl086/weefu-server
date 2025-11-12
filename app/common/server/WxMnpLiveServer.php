<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\server;


use app\common\exception\WechatException;
use EasyWeChat\Factory;

/**
 * 微信小程序直播
 * Class WxLiveServer
 * @package app\common\server
 */
class WxMnpLiveServer
{
    // wechat
    protected $app = null;

    // 初始化
    public function __construct()
    {
        $config = WeChatServer::getMnpConfig();
        $this->app = Factory::miniProgram($config);
    }


    public function handle($action, $data)
    {
        try {
            $result = $this->$action($data);
            if (0 != $result['errcode']) {
                throw new WechatException($result['errmsg'], $result['errcode']);
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * @notes 获取直播间
     * @param $params
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/17 17:45
     */
    public function getRooms($params)
    {
        return $this->app->broadcast->getRooms($params['start'], $params['limit']);
    }


    /**
     * @notes 添加直播间
     * @param $roomData
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/17 15:10
     */
    public function createLiveRoom($roomData)
    {
        return $this->app->broadcast->createLiveRoom($roomData);
    }


    /**
     * @notes 编辑直播间
     * @param $roomData
     * @return mixed
     * @author 段誉
     * @date 2023/2/17 16:05
     */
    public function editRoom($roomData)
    {
        return $this->app->httpPost('wxaapi/broadcast/room/editroom', $roomData);
    }


    /**
     * @notes 删除直播间
     * @param $roomId
     * @return mixed
     * @throws WechatException
     * @author 段誉
     * @date 2023/2/17 14:52
     */
    public function delRoom($roomId)
    {
        return $this->app->httpPostJson('wxaapi/broadcast/room/deleteroom', [
            "id" => $roomId,
        ]);
    }


    /**
     * @notes 商品添加并提审
     * @param $goodsInfo
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/17 15:10\
     */
    public function addAndAuditGoods($goodsInfo)
    {
        return $this->app->broadcast->create($goodsInfo);
    }


    /**
     * @notes 商品详情
     * @param $goodsIds
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/17 19:15
     */
    public function getGoodsWarehouse($goodsIds)
    {
        return $this->app->broadcast->getGoodsWarehouse($goodsIds);
    }


    /**
     * @notes 删除商品
     * @param $goodsIds
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/17 15:10
     */
    public function delGoods($goodsIds)
    {
        return $this->app->broadcast->delete($goodsIds);
    }


    /**
     * @notes 导入商品
     * @param $data
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/17 15:10
     */
    public function importGoods($data)
    {
        return $this->app->broadcast->addGoods($data);
    }


}