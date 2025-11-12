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


use think\exception\HttpResponseException;
use think\Response;
use think\response\Json;

/**
 * 统一Json服务类
 * Class JsonServer
 * @Author FZR
 * @package app\common\server
 */
class JsonServer
{
    private static $SUCCESS = 1; //成功状态码
    private static $Error   = 0; //失败状态码

    /**
     * 统一返回JSON格式
     * @param int $code (状态码)
     * @param int $show (显示)
     * @param string $msg (提示)
     * @param array $data (返回数据集)
     * @param int $httpStatus (异常方式抛出)
     * @Author FZR
     * @return Json
     */
    private static function result(int $code, int $show, string $msg='OK', array $data=[], int $httpStatus=200) :Json
    {
        $result = array(
            'code' => $code,
            'show' => $show,
            'msg'  => $msg,
            'data' => $data
        );
        return json($result, $httpStatus);
    }

    /**
     * 成功返回
     * @param string $msg (提示)
     * @param array $data (数据集)
     * @Author FZR
     * @return Json
     */
    public static function success(string $msg='OK', array $data=[], int $code = 1, int $show = 0) : Json
    {
        return self::result($code, $show, $msg, $data);
    }

    /**
     * 错误返回
     * @param string $msg (提示)
     * @param array $data (数据集)
     * @Author FZR
     * @return Json
     */
    public static function error(string $msg='Error', array $data=[],int $code = 0, int $show = 1) : Json
    {
        return self::result($code, $show, $msg, $data);
    }

    /**
     * Notes: 抛出JSON
     * @param string $msg
     * @param array $data
     * @param int $code
     * @Author FZR
     */
    public static function throw(string $msg='Error', array $data=[], int $code=0, int $show = 1)
    {
        $data = array('code'=>$code, 'show'=>$show, 'msg'=>$msg, 'data'=>$data);
        $response = Response::create($data, 'json', 200);
        throw new HttpResponseException($response);
    }
}
