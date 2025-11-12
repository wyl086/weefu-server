<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\server\printing;


use app\common\model\Express;
use app\common\server\AreaServer;
use app\common\server\ConfigServer;
use think\Exception;

class Kuaidi100
{
    /**
     * 客户授权key
     */
    private $key;

    /**
     * 授权secret
     */
    private $secret;

    /**
     * 设备编号
     */
    private $siid;


    public function __construct($shop_id)
    {
        $kd100 = ConfigServer::get('kd100', null, null, $shop_id);
        $this->key    = $kd100['kd100_key'] ?? '';
        $this->secret = $kd100['kd100_secret'] ?? '';
        $this->siid   = $kd100['kd100_siid'] ?? '';
    }


    /**
     * @notes 打印电子面单
     * @param $data
     * @return mixed
     * @throws Exception
     * @author 段誉
     * @date 2023/2/13 17:03
     */
    public function print($data)
    {
        try {
            if (!$this->key) {
                throw new Exception('请设置快递100的授权key');
            }
            if (!$this->secret) {
                throw new Exception('请设置快递100的授权secret');
            }
            if (!$this->siid) {
                throw new Exception('请设置打印机设备编码');
            }

            if ($data['express']['name'] != '顺丰快递') {
                if (!$data['template']['partner_id']) {
                    throw new Exception('请设置电子面单客户账号');
                }
                if (!$data['template']['partner_key']) {
                    throw new Exception('请设置电子面单客户密钥');
                }
            }
            $code = Express::getkuaidi100code($data['express']['code100']);
            // 请求参数
            $param = array(
                'printType'  => 'CLOUD',
                'partnerId'  => $data['template']['partner_id'],
                'partnerKey' => $data['template']['partner_key'],
                'net'        => $data['template']['net'] ?? $data['express']['name'],
                'kuaidicom'  => $data['express']['code100'],
                'tempid'     => $data['template']['template_id'],
                'siid'       => $this->siid,
                'cargo'      => '商品',
                'code'       => $code,      //申通快递需要code
                'weight'     => $data['total_weight'] ?: 1,
                'count'      => 1,
                'payType'    => $data['template']['pay_type'],
                'expType'    => '标准快递',
                'remark'     => $data['remark'],
                'recMan' => array(
                    'name'      => $data['order']['consignee'],
                    'mobile'    => $data['order']['mobile'],
                    'printAddr' => AreaServer::getAddress([
                        $data['order']['province'],
                        $data['order']['city'],
                        $data['order']['city'],
                        $data['order']['district'],
                    ], $data['order']['address']),
                ),
                'sendMan' => array(
                    'name'      => $data['sender']['name'],
                    'mobile'    => $data['sender']['mobile'],
                    'printAddr' => AreaServer::getAddress([
                        $data['sender']['province_id'],
                        $data['sender']['city_id'],
                        $data['sender']['district_id'],
                    ], $data['sender']['address']),
                )
            );

            // 当前时间戳
            list($msec, $sec) = explode(' ', microtime());
            $t = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

            // 格式化参数
            $post_data = array();
            $post_data["param"] = json_encode($param, JSON_UNESCAPED_UNICODE);
            $post_data["key"] = $this->key;
            $post_data["t"] = $t;
            $sign = md5($post_data["param"] . $t . $this->key . $this->secret);
            $post_data["sign"] = strtoupper($sign);

            //V2接口
            $url = 'https://api.kuaidi100.com/label/order?method=order';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = json_decode(curl_exec($ch), true);

            if (!$result || $result['code'] != '200') {
                throw new \Exception($result['message'] ?? '打印电子面单异常,原因未知');
            }
            return $result;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}