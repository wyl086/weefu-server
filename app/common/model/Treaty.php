<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\model;


use app\common\basics\Models;

/**
 * 协议模型
 * Class Treaty
 * @package app\common\model
 */
class Treaty extends Models
{
    public function setContentAttr($value,$data)
    {
        $content = $data['content'];
        if (!empty($content)) {
            $content = HtmlSetImage($content);
        }
        return $content;
    }

    public function getContentAttr($value,$data)
    {
        $content = $data['content'];
        if (!empty($content)) {
            $content = HtmlSetImage($content);
        }
        return $content;
    }
}