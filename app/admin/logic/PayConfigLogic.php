<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\logic;

use app\common\basics\Logic;
use app\common\model\Pay;
use app\common\server\UrlServer;

/**
 * 支付配置 - 逻辑
 * Class PayConfigLogic
 * @package app\admin\logic
 */
class PayConfigLogic extends Logic
{

    /**
     * Notes: 配置列表
     * @author 段誉(2021/5/7 18:15)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function lists()
    {
        $count = Pay::count();
        $lists = Pay::withAttr('status', function($value, $data) {
            return $value == 1 ? '启用' : '关闭';
        })->order('sort')->select();
        return ['lists' => $lists, 'count' => $count];
    }


    /**
     * Notes: 详情
     * @param $pay_code
     * @author 段誉(2021/5/7 18:15)
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function info($pay_code)
    {
        return Pay::where(['code' => $pay_code])->find();
    }


    /**
     * Notes: 余额
     * @param $post
     * @author 段誉(2021/5/7 18:15)
     * @return Pay
     */
    public static function editBalance($post)
    {
        return Pay::where('code', 'balance')->update([
            'short_name' => $post['short_name'],
            'image'      => UrlServer::setFileUrl($post['image'])  ?? '',
            'status'     => $post['status'],
            'sort'       => $post['sort'] ?? 0,
        ]);
    }


    /**
     * Notes: 微信
     * @param $post
     * @author 段誉(2021/5/7 18:16)
     * @return Pay
     */
    public static function editWechat($post)
    {

        $data = [
            'short_name' => $post['short_name'],
            'image'      => UrlServer::setFileUrl($post['image']) ?? '',
            'status'     => $post['status'],
            'sort'       => $post['sort'] ?? 0,
            'config'     => [
                'pay_sign_key' => $post['pay_sign_key'],
                'mch_id' => $post['mch_id'],
                'apiclient_cert' => $post['apiclient_cert'],
                'apiclient_key' => $post['apiclient_key']
            ]
        ];
        return Pay::where('code', 'wechat')->update($data);
    }


    /**
     * Notes: 支付宝
     * @param $post
     * @author 段誉(2021/5/7 18:16)
     * @return Pay
     */
    public static function editAlipay($post)
    {
        $data = [
            'short_name' => $post['short_name'],
            'image'      => UrlServer::setFileUrl($post['image']) ?? '',
            'status'     => $post['status'],
            'sort'       => $post['sort'] ?? 0,
            'config'     => [
                // 应用id
                'app_id'            => $post['app_id'],
                // 应用私钥
                'private_key'       => $post['private_key'],
                // 接口加密
                'api_type'          => $post['api_type'] ?? 'certificate',
                // 应用公钥证书
                'app_cert'          => $post['app_cert'] ?? '',
                // 支付宝公钥
                // 'ali_public_key'    => $post['ali_public_key'],
                // 支付宝公钥证书
                'ali_public_cert'   => $post['ali_public_cert'] ?? '',
                // 支付宝CA根证书
                'ali_root_cert'     => $post['ali_root_cert'] ?? '',
            ]
        ];
        return Pay::where('code', 'alipay')->update($data);
    }

    static function editHfdgWechat($post)
    {
        $data = [
            'short_name' => $post['short_name'],
            'image'      => UrlServer::setFileUrl($post['image']) ?? '',
            'status'     => $post['status'],
            'sort'       => $post['sort'] ?? 0,
        ];
        
        return Pay::where('code', 'hfdg_wechat')->update($data);
    }
    
    static function editHfdgAlipay($post)
    {
        $data = [
            'short_name' => $post['short_name'],
            'image'      => UrlServer::setFileUrl($post['image']) ?? '',
            'status'     => $post['status'],
            'sort'       => $post['sort'] ?? 0,
        ];
        
        return Pay::where('code', 'hfdg_alipay')->update($data);
    }
}