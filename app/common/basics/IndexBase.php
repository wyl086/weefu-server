<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\basics;


use think\App;
use think\exception\HttpResponseException;

/**
 * index基类
 * Class AdminBase
 * @Author mjf
 * @package app\common\basics
 */
abstract class IndexBase
{
    /**
     * Request实例
     */
    protected $request;

    /**
     * 应用实例
     */
    protected $app;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
    }


    /**
     * Notes: 自定义重定向
     * @param mixed ...$args
     * @author 段誉(2021/4/9 14:04)
     */
    public function redirect(...$args)
    {
        throw new HttpResponseException(redirect(...$args));
    }



}