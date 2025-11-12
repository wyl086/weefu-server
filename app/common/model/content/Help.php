<?php


namespace app\common\model\content;


use app\common\basics\Models;

class Help extends Models
{
    /**
     * @Notes: 关联帮助分类模型
     * @Author: 张无忌
     */
    public function category()
    {
        return $this->hasOne('HelpCategory', 'id', 'cid');
    }


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
            $content = HtmlGetImage($content);
        }
        return $content;
    }

}