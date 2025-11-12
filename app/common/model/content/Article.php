<?php


namespace app\common\model\content;


use app\common\basics\Models;

class Article extends Models
{

    /**
     * @Notes: 关联文章分类模型
     * @Author: 张无忌
     */
    public function category()
    {
        return $this->hasOne('ArticleCategory', 'id', 'cid');
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
            $content = HtmlSetImage($content);
        }
        return $content;
    }
}