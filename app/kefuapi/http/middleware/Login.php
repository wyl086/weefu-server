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

namespace app\kefuapi\http\middleware;


use app\common\model\kefu\Kefu;
use think\exception\ValidateException;
use app\kefuapi\validate\TokenValidate;

class Login
{
    /**
     * 登录验证
     * @param $request
     * @param \Closure $next
     * @return mixed|\think\response\Redirect
     */
    public function handle($request, \Closure $next)
    {
        //允许跨域调用
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Authorization, Sec-Fetch-Mode, DNT, X-Mx-ReqToken, Keep-Alive, User-Agent, If-Match, If-None-Match, If-Unmodified-Since, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Accept-Language, Origin, Accept-Encoding,Access-Token,token");
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Max-Age: 1728000');
        header('Access-Control-Allow-Credentials:true');

        if (strtoupper($request->method()) == "OPTIONS") {
            return response();
        }

        $token = $request->header('token');

        // 无需登录
        if (empty($token) && $this->isNotNeedLogin($request)) {
            return $next($request);
        }

        $validateError = '';
        try {
            validate(TokenValidate::class)->check(['token' => $token]);
            $kefu_info = (new Kefu())->alias('k')
                ->join('kefu_session ks', 'k.id = ks.kefu_id')
                ->where(['ks.token' => $token])
                ->field('k.*,ks.token,ks.client')
                ->hidden(['password'])
                ->findOrEmpty();

            $kefu_info = $kefu_info ? $kefu_info->toArray() : [];

            // 设置用户信息
            $request->kefu_info = $kefu_info;

            return $next($request);

        } catch (ValidateException $e) {
            $validateError = $e->getError();
        }

        //无需要登录，带token的情况
        if ($this->isNotNeedLogin($request) && $token) {
            return $next($request);
        }

        //登录失败
        $result = array(
            'code' => -1,
            'show' => 1,
            'msg' => $validateError,
            'data' => []
        );
        return json($result);
    }


    /**
     * @notes 是否需要登录
     * @param $request
     * @return bool // false-需要; true-不需要
     * @author 段誉
     * @date 2021/11/10 11:10
     */
    private function isNotNeedLogin($request)
    {
        $controllerObj = invoke('\\app\\kefuapi\\controller\\' . $request->controller());
        $data = $controllerObj->like_not_need_login;
        if (empty($data)) {
            return false;
        }
        return in_array($request->action(), $data);
    }

}
