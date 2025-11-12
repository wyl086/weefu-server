<?php
namespace  app\common\model\user;

use app\common\basics\Models;
use app\common\server\UrlServer;

class UserLevel extends Models
{
    public function getImageAttr($value,$data)
    {
        return empty($value) ? $value : UrlServer::getFileUrl($value);
    }

    public function getBackgroundImageAttr($value)
    {
        return empty($value) ? $value : UrlServer::getFileUrl($value);
    }
}