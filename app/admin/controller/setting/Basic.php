<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\setting;

use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use app\common\basics\AdminBase;
use app\admin\logic\setting\BasicLogic;
use app\common\server\JsonServer;

/**
 * 基础设置
 * Class Basic
 * @package app\admin\controller\setting
 */
class Basic extends AdminBase
{

    /**
     * 网站配置
     * @return mixed
     */
    public function website()
    {
        return view('', [
            'config' => BasicLogic::getBasicConfig()
        ]);
    }

    /**
     * Notes: 网站设置
     * @author 段誉(2021/6/10 20:32)
     * @return \think\response\Json
     */
    public function setWebsite()
    {
        $post = $this->request->post();
        if (empty($post['type'])) {
            return JsonServer::error('参数缺失');
        }
        if ($post['type'] == 'base') {
            BasicLogic::setWebsiteBasic($post);
        } elseif ($post['type'] == 'platform') {
            BasicLogic::setPlatform($post);
        } elseif ($post['type'] == 'shop') {
            BasicLogic::setShop($post);
        }
        return JsonServer::success('修改成功');
    }


    /**
     * Notes: 版权备案
     * @author 段誉(2021/6/10 23:55)
     * @return \think\response\View
     */
    public function copyright()
    {
        $result = BasicLogic::getCopyright();
        return view('', $result);
    }


    /**
     * Notes: 设置版权备案
     * @author 段誉(2021/6/10 23:55)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setCopyright()
    {
        $post = $this->request->post();
        $result = BasicLogic::setCopyright($post);
        if (true !== $result) {
            return JsonServer::error($result);
        }
        return JsonServer::success('修改成功');
    }


    /**
     * Notes: APP设置
     * @author 段誉(2021/6/11 1:00)
     * @return \think\response\View
     */
    public function app()
    {
        $config = [
            'line_ios' => ConfigServer::get('app', 'line_ios', ''),
            'line_android' => ConfigServer::get('app', 'line_android', ''),
            'download_doc' => ConfigServer::get('app', 'download_doc', ''),
            'agreement' => ConfigServer::get('app', 'agreement', 0),
            'wechat_login'  => ConfigServer::get('app', 'wechat_login',  0),
        ];
        return view('', ['config' => $config]);
    }

    public function setApp()
    {
        $post = $this->request->post();
        $post['agreement'] = isset($post['agreement']) && $post['agreement'] == 'on' ? 1 : 0;
        $post['wechat_login'] = isset($post['wechat_login']) && $post['wechat_login'] == 'on' ? 1 : 0;
        ConfigServer::set('app', 'line_ios',$post['line_ios']);
        ConfigServer::set('app', 'line_android',$post['line_android']);
        ConfigServer::set('app', 'download_doc',$post['download_doc']);
        ConfigServer::set('app', 'agreement',$post['agreement']);
        ConfigServer::set('app', 'wechat_login',$post['wechat_login']);
        return JsonServer::success('修改成功');
    }

    /**
     * 分享设置
     */
    public function share()
    {
        $config = [
            'file_url' => UrlServer::getFileUrl(''),
            'h5'  => ConfigServer::get('share', 'h5', [
                'h5_share_title' => '',
                'h5_share_intro' => '',
                'h5_share_image' => ''
            ]),
            'mnp' => ConfigServer::get('share', 'mnp', [
                'mnp_share_title' => '',
                'mnp_share_image' => ''
            ])
        ];
        return view('', ['config' => $config]);
    }

    public function setShare()
    {
        $post = $this->request->post();
        $h5 = json_encode([
            'h5_share_title' => $post['h5_share_title'],
            'h5_share_intro' => $post['h5_share_intro'],
            'h5_share_image' => !empty($post['h5_share_image']) ? UrlServer::setFileUrl($post['h5_share_image']) : '',
        ], JSON_UNESCAPED_UNICODE);
        $mnp = json_encode([
            'mnp_share_title' => $post['mnp_share_title'],
            'mnp_share_image' => !empty($post['mnp_share_image']) ? UrlServer::setFileUrl($post['mnp_share_image']) : '',
        ], JSON_UNESCAPED_UNICODE);
        ConfigServer::set('share', 'h5', $h5);
        ConfigServer::set('share', 'mnp', $mnp);
        return JsonServer::success('修改成功');
    }


    /**
     * Notes: 政策协议
     * @author 段誉(2021/6/11 0:41)
     * @return mixed
     */
    public function policy()
    {
        $config = [
            'service'       => HtmlGetImage(ConfigServer::get('policy', 'service')),
            'privacy'       => HtmlGetImage(ConfigServer::get('policy', 'privacy')),
            'after_sale'    => HtmlGetImage(ConfigServer::get('policy', 'after_sale')),
            'user_delete'   => HtmlGetImage(ConfigServer::get('policy', 'user_delete')),
        ];
        
        return view('', ['config' => $config]);
    }

    public function setPolicy()
    {
        $post = $this->request->post();
        if ($post) {
            ConfigServer::set('policy', 'service', HtmlSetImage($post['service']));
            ConfigServer::set('policy', 'privacy', HtmlSetImage($post['privacy']));
            ConfigServer::set('policy', 'after_sale', HtmlSetImage($post['after_sale']));
            ConfigServer::set('policy', 'user_delete', HtmlSetImage($post['user_delete']));
            return JsonServer::success('修改成功');
        }
    }

    /***
     * 会员提现设置
     */
    public  function withdraw(){
        $config = [
            'min_withdraw' => ConfigServer::get('withdraw', 'min_withdraw'),
            'max_withdraw' => ConfigServer::get('withdraw', 'max_withdraw'),
            'poundage' => ConfigServer::get('withdraw', 'poundage'),
            'type' => ConfigServer::get('withdraw', 'type') ? ConfigServer::get('withdraw', 'type') : [],
            'transfer_way' => ConfigServer::get('withdraw', 'transfer_way',1),
        ];
        return view('', ['config' => $config]);
    }

    /***
     * 会员提现设置提交
     */
    public function setWithdraw()
    {
        $post = $this->request->post();
        if(empty($post['type'])) {
            return JsonServer::error('至少选择一种提现方式');
        }
        if ($post) {
            ConfigServer::set('withdraw', 'min_withdraw', $post['min_withdraw']);//最低提现
            ConfigServer::set('withdraw', 'max_withdraw', $post['max_withdraw']);//最高提现
            ConfigServer::set('withdraw', 'poundage', $post['poundage']);//提现手续费
            ConfigServer::set('withdraw', 'type', $post['type']);//提现方式
            ConfigServer::set('withdraw', 'transfer_way', $post['transfer_way']);//微信零钱接口
            return JsonServer::success('操作成功');
        }
    }
}