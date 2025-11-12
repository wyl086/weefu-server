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

namespace app\kefuapi\controller;

use app\common\basics\KefuBase;
use app\kefuapi\logic\ChatLogic;
use app\common\server\JsonServer;
use app\kefuapi\validate\ChatValidate;


class Chat extends KefuBase
{

    public $like_not_need_login = ['config'];

    /**
     * @notes 用户列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/14 15:42
     */
    public function user()
    {
        $get = $this->request->get('');
        // 对话过的用户
        $result = ChatLogic::getChatUserList($this->kefu_id, $this->shop_id, $get, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }



    /**
     * @notes 指定用户聊天记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/14 15:43
     */
    public function record()
    {
        $user_id = $this->request->get('user_id/d');
        (new ChatValidate())->goCheck();
        $result = ChatLogic::getChatRecord($this->kefu_id, $user_id, $this->shop_id, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }



    /**
     * @notes 在线的客服
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/14 15:43
     */
    public function online()
    {
        $result = ChatLogic::getOnlineKefu($this->kefu_id, $this->shop_id);
        return JsonServer::success('', $result);
    }



    /**
     * @notes 快捷回复
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2021/12/15 11:07
     */
    public function reply()
    {
        $keyword = $this->request->get('keyword');
        $result = ChatLogic::getReplyLists($this->shop_id, $keyword, $this->page_no, $this->page_size);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 用户详情接口
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/12/15 15:05
     */
    public function userInfo()
    {
        $user_id = $this->request->get('user_id/d');
        $result = ChatLogic::getUserInfo($user_id);
        if (false === $result) {
            return JsonServer::error(ChatLogic::getError() ?: '系统错误');
        }
        return JsonServer::success('', $result);
    }


    /**
     * @notes 获取指定订单列表
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/12/15 16:04
     */
    public function order()
    {
        $get = $this->request->get();
        $result = ChatLogic::getOrderLists($get, $this->shop_id, $this->page_no, $this->page_size);
        if (false === $result) {
            return JsonServer::error(ChatLogic::getError() ?: '系统错误');
        }
        return JsonServer::success('', $result);
    }


    /**
     * @notes 客服详情
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/12/15 17:34
     */
    public function kefuInfo()
    {
        $result = ChatLogic::getKefuInfo($this->kefu_id);
        return JsonServer::success('', $result);
    }


    /**
     * @notes 配置
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/12/16 17:06
     */
    public function config()
    {
        $result = ChatLogic::getConfig();
        return JsonServer::success('', $result);
    }



}