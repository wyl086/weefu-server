<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\server\DouGong;

class BaseFunc
{
    static function json($data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
    
    static function get_data_json(array $data) : array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::json($value);
            }
        }
        
        return $data;
    }
    
    /**
     * 私钥加签（对数据源排序），可用于 V2 版本接口数据加签
     * @param int $alg 默认 OPENSSL_ALGO_SHA256
     *
     * @return string 签名串
     */
    static function sha_with_rsa_sign($data, $rsaPrivateKey, int $alg = OPENSSL_ALGO_SHA256): string
    {
        ksort($data);
        
        $data = static::json(static::get_data_json($data));
        
        $key = "-----BEGIN PRIVATE KEY-----\n" .
            wordwrap($rsaPrivateKey, 64, "\n", true) .
            "\n-----END PRIVATE KEY-----";
        
        $signature = '';
        
        openssl_sign($data, $signature, $key, $alg);
        
        return base64_encode($signature);
    }
    
    /**
     * 汇付公钥验签（对数据源排序），可用于 V2 版本接口返回数据验签
     *
     * @param string $signature 签文
     * @param string $data 原数据(array)
     * @param string $rsaPublicKey 公钥
     * @param int $alg 默认 OPENSSL_ALGO_SHA256
     *
     * @return false|int 验证结果：成功/失败
     */
    static function verifySign_sort(string $signature, string $data, string $rsaPublicKey, int $alg = OPENSSL_ALGO_SHA256)
    {
        $key = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($rsaPublicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        
        return openssl_verify($data, base64_decode($signature), $key, $alg);
    }
}