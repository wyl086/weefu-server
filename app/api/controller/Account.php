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

use app\api\logic\LoginLogic;
use app\api\validate\WechatLoginValidate;
use app\common\basics\Api;
use app\common\enum\NoticeEnum;
use app\common\server\JsonServer;
use app\common\server\ConfigServer;
use app\api\validate\RegisterValidate;
use app\api\validate\LoginValidate;
use think\exception\ValidateException;
use app\api\validate\OaLoginValidate;

class Account extends Api
{

    public $like_not_need_login = ['silentLogin', 'authLogin', 'register', 'login','mnplogin', 'codeurl', 'oalogin', 'oplogin','logout','smslogin', 'uinAppLogin', 'silentLogin', 'authLogin', 'scanCode', 'scanLogin'];

    /**
     * 注册
     */
    public function register()
    {
        $post  = $this->request->post();
        $post['check_code'] = ConfigServer::get('register', 'captcha', 0);
        $post['message_key'] = NoticeEnum::REGISTER_NOTICE;
        try{
            validate(RegisterValidate::class)->check($post);
        }catch(ValidateException $e) {
            return JsonServer::error($e->getError());
        }

        $result = LoginLogic::register($post);
        if($result !== false) {
            return JsonServer::success('注册成功', $result);
        }
        return JsonServer::error('注册失败:'.LoginLogic::getError());
    }



    /**
     * Notes: 小程序登录(旧系统用户,返回用户信息,否则返回空)
     * @author 段誉(2021/4/19 16:50)
     */
    public function silentLogin()
    {
        $post = $this->request->post();
        if (empty($post['code'])) {
            return JsonServer::error('参数缺失');
        }
        $data = LoginLogic::silentLogin($post);
        if(false === $data) {
            $error = LoginLogic::getError() ?? '登录失败';
            return JsonServer::error($error);
        }
        return JsonServer::success('', $data);
    }

    /**
     * Notes: 小程序登录(新用户登录->需要提交昵称和头像参数)
     * @author 段誉(2021/4/19 16:49)
     */
    public function authLogin()
    {
        $post = $this->request->post();
        if (empty($post['code']) || empty($post['nickname'])) {
            return JsonServer::error('参数缺失');
        }

        $data = LoginLogic::authLogin($post);
        if(false === $data) {
            $error = LoginLogic::getError() ?? '登录失败';
            return JsonServer::error($error);
        }
        return JsonServer::success('', $data);
    }

    /**
     * 手机号密码登录
     */
    public function login()
    {
        $post = $this->request->post();
        (new LoginValidate)->goCheck('mpLogin', $post);
        $data = LoginLogic::mpLogin($post);
        return JsonServer::success('登录成功',$data);
    }

    /**
     * showdoc
     * @catalog 接口/账号
     * @title 获取获取向微信请求code的链接
     * @description
     * @method get
     * @url /account/codeurl
     * @param url 必填 varchar 前端当前url
     * @return_param url string codeurl
     * @remark 这里是备注信息
     * @number 0
     * @return  {"code":1,"msg":"获取成功","data":{"url":'http://mp.weixin……'}}
     */
    public function codeUrl()
    {
        $url = $this->request->get('url');
        return JsonServer::success('获取成功', ['url' => LoginLogic::codeUrl($url)]);
    }

    /**
     * showdoc
     * @catalog 接口/账号
     * @title 微信H5登录
     * @description 微信H5登录
     * @method post
     * @url /account/oalogin
     * @return {"code":1,"msg":"登录成功","data":["token":"3237676fa733d73333341",//登录令牌"nickname":"好象cms-小林",//昵称"avatar":"http://b2c.yixiangonline.com/uploads/user/avatar/3f102df244d5b40f21c4b25dc321c5ab.jpeg",//头像url"level":0,//等级],"show":0,"time":"0.775400"}
     * @param code 必填 string code
     * @return_param token string 登录令牌
     * @return_param nickname string 昵称
     * @return_param avatar string 头像
     * @remark
     * @number 1
     */
    public function oaLogin()
    {
        $post = $this->request->post();
        (new OaLoginValidate())->check($post);
        $data = LoginLogic::oaLogin($post);
        return JsonServer::success('登录成功', $data);
    }

    /**
     * Notes: uniapp微信登录
     */
    public function uinAppLogin()
    {
        $post = $this->request->post();
        $data = LoginLogic::uinAppLogin($post);
        if(is_string($data )){
            return JsonServer::error($data);
        }
        $data = [
            'code' => 1,
            'show' => 0,
            'msg' => '登录成功',
            'data' => $data
        ];
        return json($data);
    }

    /***
     * 短信登录
     * @return \think\response\Json
     */
    public function smsLogin()
    {
        $post = $this->request->post();
        (new LoginValidate())->goCheck('smsLogin');
        $data = LoginLogic::login($post);
        return JsonServer::success('登录成功', $data);
    }

    /***
     * 退出登录
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function logout()
    {
        LoginLogic::logout($this->user_id, $this->client);
        //退出登录只有成功
        return JsonServer::success();
    }


    /**
     * @notes 扫码登录
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/10/29 11:50
     */
    public function scanCode()
    {
        $result = LoginLogic::scanCode();
        if (false === $result) {
            return JsonServer::error(LoginLogic::getError() ?? '未知错误');
        }
        return JsonServer::success('', ['url' => $result]);
    }


    /**
     * @notes PC端扫码登录
     * @return \think\response\Json
     * @author 段誉
     * @date 2021/10/29 18:00
     */
    public function scanLogin()
    {
        $params = $this->request->post();
        $result = LoginLogic::scanLogin($params);
        if (false === $result) {
            return JsonServer::error(LoginLogic::getError() ?? '未知错误');
        }
        return JsonServer::success('', $result);
    }

    /**
     * @notes 更新用户头像昵称
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @author ljj
     * @date 2023/2/1 3:46 下午
     */
    public function updateUser()
    {
        $post = $this->request->post();
        if (!isset($post['avatar']) || empty($post['avatar'])) {
            JsonServer::error('参数缺失');
        }
        if (!isset($post['nickname']) || empty($post['nickname'])) {
            JsonServer::error('参数缺失');
        }

        LoginLogic::updateUser($post,$this->user_id);
        return JsonServer::success('操作成功');
    }
    
    /**
     * @notes 小程序绑定微信
     * @return mixed
     * @author lbzy
     * @datetime 2023-10-30 11:13:05
     */
    public function mnpAuthLogin()
    {
        $params = (new WechatLoginValidate())->goCheck("wechatAuth");
        $params['user_id'] = $this->user_id;
        $result = (new LoginLogic())->mnpAuthLogin($params);
        if ($result === false) {
            return JsonServer::error(LoginLogic::getError());
        }
        return JsonServer::success('绑定成功', [], 1, 1);
        
    }
    
    /**
     * @notes 公众号绑定微信
     * @return mixed
     * @author lbzy
     * @datetime 2023-10-30 11:12:59
     */
    public function oaAuthLogin()
    {
        $params = (new WechatLoginValidate())->goCheck("wechatAuth");
        $params['user_id'] = $this->user_id;
        $result = (new LoginLogic())->oaAuthLogin($params);
        if ($result === false) {
            return JsonServer::error(LoginLogic::getError());
        }
        
        return JsonServer::success('绑定成功', [], 1, 1);
    }


}