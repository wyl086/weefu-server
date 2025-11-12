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


use app\admin\validate\setting\StorageValidate;
use app\common\basics\AdminBase;
use app\common\server\ConfigServer;
use app\common\server\JsonServer;

/**
 * 上传设置
 * Class StorageConfig
 * @package app\admin\controller
 */
class StorageConfig extends AdminBase
{
    /**
     * Notes: 存储引擎列表
     * @author 张无忌(2021/2/22 11:43)
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $default = ConfigServer::get('storage', 'default', '');
            $data = [
                [
                    'name'   => '本地存储',
                    'path'   => '存储在本地服务器',
                    'engine' => 'local',
                    'status' => $default == 'local' ? 1 : 0
                ],
                [
                    'name'   => '七牛云存储',
                    'path'   => '存储在七牛云，请前往七牛云开通存储服务',
                    'engine' => 'qiniu',
                    'status' => $default == 'qiniu' ? 1 : 0
                ],
                [
                    'name'   => '阿里云OSS',
                    'path'   => '存储在阿里云，请前往阿里云开通存储服务',
                    'engine' => 'aliyun',
                    'status' => $default == 'aliyun' ? 1 : 0
                ],
                [
                    'name'   => '腾讯云OSS',
                    'path'   => '存储在腾讯云，请前往腾讯云开通存储服务',
                    'engine' => 'qcloud',
                    'status' => $default == 'qcloud' ? 1 : 0
                ]
            ];
            return JsonServer::success('获取成功', ['lists' => $data]);
        }
        return view();
    }

    /**
     * Notes: 编辑存储引擎
     * @author 张无忌(2021/2/22 11:43)
     * @return mixed
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $engine= $this->request->post('engine');
            $post = $this->request->post();
            
            if ($engine != 'local') {
                $post['domain'] = $post[$engine . '_domain'] ?? '';
                $validate = new StorageValidate();
                if (! $validate->scene('edit')->check($post)) {
                    return JsonServer::error('设置失败:' . $validate->getError());
                }
            }

            if ($engine === 'qiniu') {

                try {
                    ConfigServer::set('storage_engine', 'qiniu', [
                        'bucket'     => $post['qiniu_bucket'],
                        'access_key' => $post['qiniu_ak'],
                        'secret_key' => $post['qiniu_sk'],
                        'domain'     => $post['qiniu_domain']
                    ]);
                } catch (\Exception $e) {
                    return JsonServer::error('设置失败:'.$e->getMessage());
                }
                return JsonServer::success('设置成功');

            } elseif ($engine === 'aliyun') {

                try {
                    ConfigServer::set('storage_engine', 'aliyun', [
                        'bucket'            => $post['aliyun_bucket'],
                        'access_key_id'     => $post['aliyun_ak'],
                        'access_key_secret' => $post['aliyun_sk'],
                        'domain'            => $post['aliyun_domain']
                    ]);
                } catch (\Exception $e) {
                    return JsonServer::error('设置失败:'.$e->getMessage());
                }
                return JsonServer::success('设置成功');

            } elseif ($engine === 'qcloud') {

                try {
                    ConfigServer::set('storage_engine', 'qcloud', [
                        'bucket'     => $post['qcloud_bucket'],
                        'region'     => $post['qcloud_region'],
                        'secret_id'  => $post['qcloud_ak'],
                        'secret_key' => $post['qcloud_sk'],
                        'domain'     => $post['qcloud_domain']
                    ]);
                } catch (\Exception $e) {
                    return JsonServer::error('设置失败:'.$e->getMessage());
                }
                return JsonServer::success('设置成功');
            }
            return JsonServer::error('您设置的存储引擎不存在');
        }

        $engine = $this->request->get('engine');
        $storage = [
            'qiniu' => ConfigServer::get('storage_engine', 'qiniu', [
                'bucket'     => '',
                'access_key' => '',
                'secret_key' => '',
                'domain'     => 'http://'
            ]),
            'aliyun' => ConfigServer::get('storage_engine', 'aliyun', [
                'bucket'     => '',
                'access_key_id' => '',
                'access_key_secret' => '',
                'domain'     => 'http://'
            ]),
            'qcloud' => ConfigServer::get('storage_engine', 'qcloud', [
                'bucket'     => '',
                'region'     => '',
                'secret_id'  => '',
                'secret_key' => '',
                'domain'     => 'http://'
            ])
        ];
        return view('', [
            'engine' => $engine,
            'storage' => $storage,
        ]);
    }

    /**
     * Notes: 切换存储引擎
     * @author 张无忌(2021/2/22 11:43)
     */
    public function changeEngine()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            try {
                ConfigServer::set('storage', 'default', $post['engine']);
            } catch (\Exception $e) {
                return JsonServer::error('切换失败:'.$e->getMessage());
            }
            return JsonServer::success('切换成功');
        }
    }
}