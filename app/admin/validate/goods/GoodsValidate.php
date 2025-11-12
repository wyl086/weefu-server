<?php
namespace  app\admin\validate\goods;

use think\Validate;

class GoodsValidate extends Validate
{
    protected $rule = [
        'goods_id' => 'require|integer',
        'reason' => 'require|length:1,200',
        'sales_virtual' => 'integer|egt:0',
        'clicks_virtual' => 'integer|egt:0',
        'sort_weight' => 'integer|egt:0',
        'audit_status' => 'require|integer|in:1,2',
        'audit_remark' => 'require|length:1,200',
        'ids' => 'require',
    ];

    protected  $message= [
        'goods_id.require' => '商品id不能为空',
        'goods_id.integer' => '商品id须为整型',
        'reason.require' => '违规原因不能为空',
        'reason.length' => '违规原因不能超过200个字符',
        'sales_virtual.integer' => '虚拟销量须为整型',
        'sales_virtual.egt' => '虚拟销量须大于或等于0',
        'clicks_virtual.integer' => '虚拟浏览量须为整型',
        'clicks_virtual.egt' => '虚拟浏览量须大于或等于0',
        'sort_weight.integer' => '排序权重须为整型',
        'sort_weight.egt' => '排序权重须大于或等于0',
        'audit_status.require' => '审核状态不能为空',
        'audit_status.integer' => '审核状态须为整型',
        'audit_status.in' => '审核状态错误',
        'audit_remark.require' => '审核说明不能为空',
        'audit_remark.length' => '审核说明长度不能超过200个字符',
        'ids.require' => '参数缺失',
    ];

    protected $scene = [
        're_audit' => ['goods_id', 'reason'],
        'set_info' => ['goods_id', 'sales_virtual', 'clicks_virtual','sort_weight'],
        'audit' => ['goods_id', 'audit_status', 'audit_remark'],
        'moreLower' => ['ids', 'reason'],
        'moreAudit' => ['ids', 'audit_remark']
    ];
}