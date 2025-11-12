<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\api\controller;

use app\common\server\FileServer;
use app\common\basics\Api;
use app\common\server\JsonServer;

class File extends Api
{
    public $like_not_need_login = [];
    /**
     * showdoc
     * @catalog 接口/上传文件
     * @title form表单方式上传图片
     * @description 图片上传
     * @method post
     * @url /file/formimage
     * @return param name string 图片名称
     * @return param url string 文件地址
     * @remark
     * @number 1
     * @return {"code":1,"msg":"上传文件成功","data":{"url":"http:\/\/likeb2b2c.yixiangonline.com\/uploads\/images\/user\/20200810\/3cb866f6bb30b7239d91582f7d9822d6.png","name":"2.png"},"show":0,"time":"0.283254","debug":{"request":{"get":[],"post":[],"header":{"content-length":"103132","content-type":"multipart\/form-data; boundary=--------------------------206668736604428806173438","connection":"keep-alive","accept-encoding":"gzip, deflate, br","host":"www.likeb2b2c.com:20002","postman-token":"1f8aa4dd-f53c-4d12-98b4-8d901ac847db","cache-control":"no-cache","accept":"*\/*","user-agent":"PostmanRuntime\/7.26.2"}}}}
     */
    public function formImage()
    {
        try{
            $data = FileServer::image(0, 0, $this->user_id, 'uploads/user/'.$this->user_id);
            return JsonServer::success('上传成功', $data);
        }catch(\Exception $e) {
            return JsonServer::error($e->getMessage());
        }

    }
}