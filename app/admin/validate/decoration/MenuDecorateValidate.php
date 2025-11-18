<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\validate\decoration;
use app\common\basics\Validate;
use app\common\enum\MenuEnum;
use app\common\model\goods\GoodsCategory;

class MenuDecorateValidate extends Validate{
    protected $rule = [
        'id'        => 'require',
        'name'      => 'require|unique:menu_decorate,name^del^decorate_type',
        'image'     => 'require',
        'menu'      => 'checkMenu',
        'sort'      => 'integer|gt:0'
    ];
    protected $message = [
        'id.require'        => '请选择菜单',
        'name.require'      => '请输入菜单名称',
        'name.unique'       => '菜单名称重复',
        'image.unique'      => '请上传图标',
        'sort.integer'      => '排序值须为整型',
        'sort.gt'      => '排序值须大于0',
    ];

    public function sceneAdd(){
        return $this->remove('id',['require']);
    }

    public function sceneEdit(){
        return $this->remove('id',['require']);
    }

    public function sceneDel(){
        return $this->only(['id']);
    }

    public function sceneSwtich(){
        return $this->only(['id']);
    }

    public function checkMenu($value,$rule,$data){
        if( 1 == $data['link_type']){
            $menu_list = MenuEnum::INDEX;

            if(2 == $data['type']){
                $menu_list = MenuEnum::CENTRE;
            }
            $menu_index = array_column($menu_list,'index');

            if(!in_array($value,$menu_index)) {
                return '菜单不存在';
            }

        } elseif (2 == $data['link_type']) {
            if(empty(trim($data['url'] ?? ''))){
                return '请输入自定义链接';
            }
        } elseif (3 == $data['link_type']) {
            if(empty($data['appid'] ?? '')){
                return '请输入跳转小程序的appid';
            }
            if(empty(trim($data['mini_url'] ?? ''))){
                return '请输入小程序链接地址';
            }
        } elseif (4 == $data['link_type']) {
            $categoryId = intval($data['category_id'] ?? 0);
            if ($categoryId <= 0) {
                return '请选择商品分类';
            }
            $categoryExists = GoodsCategory::where([['id', '=', $categoryId], ['del', '=', 0]])->count();
            if (empty($categoryExists)) {
                return '商品分类不存在';
            }
            if (empty(trim($data['category_url'] ?? ''))) {
                return '请输入跳转链接';
            }
        }

        return true;
    }
}
