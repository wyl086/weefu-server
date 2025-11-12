<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\shop\validate\kefu;
use app\common\basics\Validate;
use app\common\model\kefu\KefuLang;

/**
 *
 * Class KefuLangValidate
 * @package app\admin\validate\kefu
 */
class KefuLangValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|checkLang',
        'title'         => 'require|unique:'.KefuLang::class.',title^shop_id',
        'content'       => 'require|unique:'.KefuLang::class.',content^shop_id',
        'sort'          => 'gt:0',
    ];

    protected $message = [
        'title.require'     => '请输入标题',
        'title.unique'      => '标题已存在',
        'content.require'   => '请输入内容',
        'content.unique'    => '内容已存在',
        'sort.gt'           => '排序不能小于零',
    ];


    public function sceneAdd()
    {
        $this->remove('id', true);
    }

    public function sceneDel()
    {
        $this->only(['id']);
    }


    public function checkLang($value,$rule,$data){
        $lang = KefuLang::where(['id'=>$value,'shop_id'=>$data['shop_id']])->find();
        if($lang){
            return true;
        }
        return '话术不存在';
    }




}