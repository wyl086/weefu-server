<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\admin\validate\kefu;
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
        'title'         => 'require|unique:'.KefuLang::class.',title',
        'content'       => 'require|unique:'.KefuLang::class.',content',
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
        $lang = KefuLang::where(['id'=>$value])->find();
        if($lang){
            return true;
        }
        return '话术不存在';
    }




}