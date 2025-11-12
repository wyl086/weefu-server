<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\common\validate;

use app\common\basics\Validate;

class UploadValidate extends Validate
{

    protected $rule = [
        'file' => 'fileExt:jpg,jpeg,gif,png,bmp,tga,tif,pdf,psd,avi,mp4,mp3,wmv,mpg,mpeg,mov,rm,ram,swf,flv,pem,ico',
    ];

    protected $message = [
        'file.fileExt' => '该文件类型不允许上传',
    ];

}