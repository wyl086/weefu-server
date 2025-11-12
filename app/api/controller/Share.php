<?php

namespace app\api\controller;

use app\api\validate\MakeMnQrcode;
use app\common\basics\Api;
use app\common\server\JsonServer;
use app\api\logic\ShareLogic;
use app\api\validate\BargainValidate;

/**
 * Class Share
 * @package app\api\controller
 */
class Share extends Api
{
    /**
     * @notes 分享商品海报
     * @return array|string|\think\response\Json
     * @author suny
     * @date 2021/7/13 6:15 下午
     */
    public function shareGoods()
    {

        $id = $this->request->get('id');
        $url = $this->request->get('url');
        $client = $this->client;
        if ($id && $url) {
            $result = ShareLogic::shareGoods($this->user_id, $id, $url, $client);
            return $result;
        }
        return JsonServer::error('参数缺失');
    }

    /**
     * @notes 用户分销海报
     * @return array|string|\think\response\Json
     * @author suny
     * @date 2021/7/13 6:15 下午
     */
    public function userPoster()
    {

        $url = $this->request->get('url');
        $client = $this->client;
        if (empty($client)) {
            return '参数缺失';
        }
        $result = ShareLogic::getUserPoster($this->user_id, $url, $client);
        return $result;
    }

    /**
     * @notes 砍价分享海报
     * @return array|\think\response\Json
     * @author suny
     * @date 2021/7/13 6:15 下午
     */
    public function shareBargain()
    {

        $id = $this->request->get('id');
        $url = $this->request->get('url');
        $client = $this->client;
        (new BargainValidate())->goCheck('share', ['id' => $id, 'url' => $url]);
        $result = ShareLogic::shareBargain($this->user_id, $id, $url, $client);
        return $result;
    }


    /**
     * @notes 获取二维码
     * @author cjhao
     * @date 2021/11/25 10:47
     */
    public function getMnQrcode()
    {
        (new MakeMnQrcode())->goCheck();
        $get = $this->request->get();
        $result = ShareLogic::getMnQrcode($this->user_id,$get);
        if(1 === $result['code']){
            return JsonServer::success('',$result['data']);
        }
        return JsonServer::error($result['msg']);


    }
}