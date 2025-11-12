<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\basics;


use app\common\server\JsonServer;
use think\response\Json;

/**
 * 验证器基类
 * Class Validate
 * @Author FZR
 * @package app\common\basics
 */
abstract class Validate extends \think\Validate
{
    /**
     * 切面验证接收到的参数
     * @param null $scene (场景验证)
     * @param array $validate_data 验证参数，可追加和覆盖掉接收的参数
     * @author FZR
     * @return mixed|Json
     */
    public function goCheck($scene=null,$validate_data = [])
    {
        // 1.接收参数
        $params = request()->param();
        //合并验证参数
        $params = array_merge($params,$validate_data);

        // 2.验证参数
        if (!($scene ? $this->scene($scene)->check($params) : $this->check($params))) {
            $exception = is_array($this->error)
                ? implode(';', $this->error) : $this->error;
            JsonServer::throw($exception);
        }
        // 3.成功返回数据
        return $params;
    }
}