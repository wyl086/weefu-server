<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------

namespace app\common\http\middleware;

/**
 * 基础中间件
 * Class BaseMiddleware
 * @package app\common\http\middleware
 */
class BaseMiddleware
{
    public function handle($request, \Closure $next)
    {
        //过滤前后空格
        $request->filter(['trim']);

        return $next($request);
    }
}