<?php

namespace app\api\validate;


use app\common\basics\Validate;

class UserAddressValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer',
        'contact'       => 'require',
        'telephone'     => 'require|mobile',
        'province_id'   => 'require',
        'city_id' => 'require',
        'district_id' => 'require',
        'address'       => 'require',
        'is_default'    => 'require',
    ];

    protected $message = [
        'id.require'            => 'id不能为空',
        'id.integer'            => 'id参数错误',
        'contact.require'       => '收货人不能为空',
        'telephone.require'     => '联系方式不能为空',
        'telephone.mobile'      => '非有效手机号',
        'province_id.require'   => '所选地区不能为空',
        'city_id.require'       => '请选择完整地址',
        'district_id.require'   => '请选择完整地址',
        'address.require'       => '详细地址不能为空',
        'is_default.require'    => '是否默认不能为空',
        'province.require'      => '省不能为空',
        'city.require'          => '市不能为空',
        'district.require'      => '区不能为空',
    ];

    protected $scene = [
        'add' => ['contact','telephone','province_id','city_id','district_id','is_default','address'],
        'set'=>['id'],
        'one'=>['id'],
        'edit'=>['id','contact','telephone','province_id','city_id','district_id','is_default'],
        'del'=>['id'],
    ];


    /**
     * 获取省市区id
     */
    public function sceneHandleRegion()
    {
        return $this->only(['province','city','district'])
            ->append('province','require')
            ->append('city','require')
            ->append('district','require');
    }
}
