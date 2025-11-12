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

use app\common\enum\FileEnum;
use app\common\model\File;
use app\common\server\storage\Driver as StorageDriver;
use EasyWeChat\Factory;
use think\Exception;

class FileServer
{
    /**
     * NOTE: 图片上传
     * @author: 张无忌
     * @param $cid
     * @param string $save_dir
     * @return array
     * @throws Exception
     */
    public static function image($cid, $shop_id, $user_id = 0,$save_dir='uploads/images')
    {
        try {
            // 1、获取当前存储对象
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine' => ConfigServer::get('storage_engine') ?? ['local'=>[]]
            ];

            // 2、执行文件上传
            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            $fileName = $StorageDriver->getFileName();
            $fileInfo = $StorageDriver->getFileInfo();

            // 校验上传文件后缀
            if (!in_array(strtolower($fileInfo['ext']), config('project.file_image'))) {
                throw new Exception("上传图片不允许上传". $fileInfo['ext'] . "文件");
            }

            // 上传文件
            $save_dir = $save_dir . '/' .  date('Ymd');
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception($StorageDriver->getError());
            }

            // 3、处理文件名称
            if (strlen($fileInfo['name']) > 128) {
                $file_name = substr($fileInfo['name'], 0, 123);
                $file_end = substr($fileInfo['name'], strlen($fileInfo['name'])-5, strlen($fileInfo['name']));
                $fileInfo['name'] = $file_name.$file_end;
            }

            // 4、写入数据库中
            $file = File::create([
                'cid'  => $cid,
                'type' => FileEnum::IMAGE_TYPE,
                'name' => $fileInfo['name'],
                'uri'  => $save_dir . '/' . str_replace("\\","/", $fileName),
                'shop_id' => $shop_id,
                'user_id' => $user_id,
                'create_time' => time(),
            ]);

            // 5、返回结果
            return [
                'id'   => $file['id'],
                'cid'  => $file['cid'],
                'type' => $file['type'],
                'name' => $file['name'],
                'shop_id' => $file['shop_id'],
                'uri'  => UrlServer::getFileUrl($file['uri']),
                'base_uri'  => clearDomain($file['uri'])
            ];

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 视频上传
     */
    public static function video($cid, $shop_id, $save_dir='uploads/video')
    {
        try {
            // 1、获取当前存储对象
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine' => ConfigServer::get('storage_engine') ?? ['local'=>[]]
            ];

            // 2、执行文件上传
            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            $fileName = $StorageDriver->getFileName();
            $fileInfo = $StorageDriver->getFileInfo();

            // 校验上传文件后缀
            if (!in_array(strtolower($fileInfo['ext']), config('project.file_video'))) {
                throw new Exception("上传视频不允许上传". $fileInfo['ext'] . "文件");
            }

            // 上传文件
            $save_dir = $save_dir . '/' .  date('Ymd');
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception($StorageDriver->getError());
            }

            // 3、处理文件名称
            if (strlen($fileInfo['name']) > 128) {
                $file_name = substr($fileInfo['name'], 0, 123);
                $file_end = substr($fileInfo['name'], strlen($fileInfo['name'])-5, strlen($fileInfo['name']));
                $fileInfo['name'] = $file_name.$file_end;
            }

            // 4、写入数据库中
            $file = File::create([
                'cid'  => $cid,
                'type' => FileEnum::VIDEO_TYPE,
                'name' => $fileInfo['name'],
                'uri'  => $save_dir . '/' . str_replace("\\","/", $fileName),
                'shop_id' => $shop_id,
                'create_time' => time(),
            ]);

            // 5、返回结果
            return [
                'id'   => $file['id'],
                'cid'  => $file['cid'],
                'type' => $file['type'],
                'name' => $file['name'],
                'shop_id' => $file['shop_id'],
                'uri'  => UrlServer::getFileUrl($file['uri'])
            ];

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Notes: 用户上传图片
     * @param string $save_dir (保存目录, 不建议修改, 不要超二级目录)
     * @param bool $isLocal (是否存不使用oss 只存本地, 上传退款证书会用到)
     * @return array
     * @author 张无忌(2021/2/20 9:53)
     */
    public static function other($save_dir='uploads/other', $isLocal=false )
    {
        try {
            if ($isLocal == false) {
                $config = [
                    'default' => ConfigServer::get('storage', 'default', 'local'),
                    'engine'  => ConfigServer::get('storage_engine')
                ];
            } else {
                $config = [
                    'default' => 'local',
                    'engine'  => ConfigServer::get('storage_engine')
                ];
            }
            if (empty($config['engine']['local'])) {
                $config['engine']['local'] = [];
            }
            $StorageDriver = new StorageDriver($config);
            $StorageDriver->setUploadFile('file');
            if (!$StorageDriver->upload($save_dir)) {
                throw new Exception('上传失败' . $StorageDriver->getError());
            }
            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();
            // 信息
            $data = [
                'name'        => $fileInfo['name'],
                'type'        => FileEnum::OTHER_TYPE,
                'uri'         => $save_dir . '/' . str_replace("\\","/", $fileName),
                'create_time' => time(),
                'shop_id' => 0
            ];
            File::insert($data);
            return ['上传文件成功', $data];

        } catch (\Exception $e) {
            $message = lang($e->getMessage()) ?? $e->getMessage();
            return ['上传文件失败:',$message];
        }
    }



    /**
     * @notes 微信直播素材
     * @param $filePath
     * @return mixed
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author 段誉
     * @date 2023/2/15 17:28\
     */
    public static function wechatLiveMaterial($filePath)
    {
        try {
            $fileName = basename($filePath);
            $config = WeChatServer::getMnpConfig();
            $app = Factory::miniProgram($config);
            $localFileDir = public_path() . pathinfo(parse_url($filePath, PHP_URL_PATH),  PATHINFO_DIRNAME) . '/';
            $localFile = $localFileDir . $fileName;

            // 切换oss后可能不存在，在本地查找后下载
            $config = [
                'default' => ConfigServer::get('storage', 'default', 'local'),
                'engine'  => ConfigServer::get('storage_engine')
            ];
            if ($config['default'] != 'local' && !file_exists($localFile)) {
                $res = download_file(UrlServer::getFileUrl($filePath), $localFileDir, $fileName);
                if (empty($res)) {
                    throw new Exception("资源下载失败");
                }
            }

            // 上传微信
            $result = $app->media->uploadImage($localFile);
            if (isset($result['errcode']) && 0 != $result['errcode']) {
                $err = $result['errmsg'] ?? '微信上传图片失败';
                throw new \Exception($err);
            }
            return $result['media_id'];
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}