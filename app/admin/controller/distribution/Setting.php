<?php
namespace app\admin\controller\distribution;

use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;

class Setting extends AdminBase
{
    /**
     * 分销设置
     */
    public function setting()
    {
        if($this->request->isPost()) {
            $post = $this->request->post();
            ConfigServer::set('distribution', 'is_open', $post['is_open']);
            ConfigServer::set('distribution', 'member_apply', $post['member_apply']);
            if(isset($post['image'])) {
                // 图片链接去除域名再入库
                $domain = $this->request->domain();
                $post['image'] = str_replace($domain, '', $post['image']);
                ConfigServer::set('distribution', 'image', $post['image']);
            }else{
                ConfigServer::set('distribution', 'image', '');
            }
            return JsonServer::success('设置成功');
        }
        $config = [
            'is_open' => ConfigServer::get('distribution', 'is_open', 1),
            'member_apply' => ConfigServer::get('distribution', 'member_apply', 1),
            'image' => ConfigServer::get('distribution', 'image', ''),
        ];
        return view('', ['config' => $config]);
    }
}