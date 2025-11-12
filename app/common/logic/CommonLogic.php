<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\common\logic;
use app\common\server\UrlServer;
use think\facade\Db;
class CommonLogic{
    /**
     * note 修改指定表的某个字段
     * author cjh 2020/10/14 14:51
     * @param $table 表名
     * @param $pk_name  id
     * @param $pk_value id的值
     * @param $field 需要修改的字段
     * @param $field_value  需要修改的值
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function changeTableValue($table,$pk_name,$pk_value,$field,$field_value){
        //允许修改的字段
        $allow_field = [
            'is_show','sort','status','is_new','is_best','is_like','is_recommend', 'del'
        ];
        if(!in_array($field,$allow_field)){
            return false;
        }
        if(is_array($pk_value)){
            $where[] = [$pk_name,'in',$pk_value];
        }else{
            $where[] = [$pk_name,'=',$pk_value];
        }

        $data= [
            $field          => $field_value,
            'update_time'   => time(),
        ];

        return Db::name($table)->where($where)->update($data);
    }

}