<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------

namespace app\common\cache;


class ExportCache extends CacheBase
{

    protected $uniqid = '';

    public function __construct($key = '')
    {
        parent::__construct($key);
        //以微秒计的当前时间，生成一个唯一的 ID,以tagname为前缀
        $this->uniqid = md5(uniqid($this->name,true).mt_rand());
    }


    /**
     * @notes 获取缓存目录
     * @return string
     * @author 段誉
     * @date 2022/4/21 11:17
     */
    public function getSrc()
    {
        return app()->getRootPath() . 'runtime/file/export/'.date('Y-m').'/'.$this->uniqid.'/';
    }


    /**
     * @notes 设置文件路径缓存地址
     * @param $fileName
     * @return string
     * @author 令狐冲
     * @date 2021/7/28 17:36
     */
    public function setFile($fileName)
    {
        $src = $this->getSrc();
        $key = md5($src . $fileName) . time();
        $this->cacheSet($key, ['src' => $src, 'name' => $fileName], 300);
        return $key;
    }


    /**
     * @notes 获取文件
     * @param $key
     * @return mixed
     * @author 段誉
     * @date 2022/4/21 11:44
     */
    public function getFile($key)
    {
        return $this->cacheGet($key);
    }

    /**
     * @notes 设置标签
     * @author 段誉
     * @date 2022/4/21 11:45
     */
    public function setTag()
    {
        return 'export';
    }

    public function setData()
    {
    }

}