<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller\finance;

use app\common\basics\AdminBase;
use app\admin\logic\finance\FinanceLogic;
use think\facade\View;

/**
 * 财务中心
 * Class Finance
 * @package app\admin\controller\finance
 */
class Finance extends AdminBase
{
    public function center()
    {

        View::assign('shop', FinanceLogic::shop());
        return view();
    }
}