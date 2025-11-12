<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\server;


/**
 * 数组转换 服务类
 * Class ArrayServer
 * @Author FZR
 * @package app\common\basics
 */
class ArrayServer
{

    /**
     * Notes: 线性结构转换成树形结构
     * @param $data 线行结构数组
     * @param string $sub_key_name 自动生成子数组名
     * @param string $id_name 数组id名
     * @param string $parent_id_name 数组祖先id名
     * @param int $parent_id 此值请勿给参数
     * @return array
     * @author 段誉(2021/4/9 15:12)
     */
    public static function linear_to_tree($data, $sub_key_name = 'sub', $id_name = 'id', $parent_id_name = 'pid', $parent_id = 0)
    {
        $tree = [];
        foreach ($data as $row) {
            if ($row[$parent_id_name] == $parent_id) {
                $temp = $row;
                $temp[$sub_key_name] = self::linear_to_tree($data, $sub_key_name, $id_name, $parent_id_name, $row[$id_name]);
                $tree[] = $temp;
            }
        }
        return $tree;
    }


    /**
     * Notes: 多级线性结构排序
     * @param $data
     * @param string $symbol
     * @param string $name
     * @param string $id_name
     * @param string $parent_id_name
     * @param int $level
     * @param int $parent_id
     * @return array
     *  * 转换前：
     * [{"id":1,"pid":0,"name":"a"},{"id":2,"pid":0,"name":"b"},{"id":3,"pid":1,"name":"c"},
     * {"id":4,"pid":2,"name":"d"},{"id":5,"pid":4,"name":"e"},{"id":6,"pid":5,"name":"f"},
     * {"id":7,"pid":3,"name":"g"}]
     * 转换后：
     * [{"id":1,"pid":0,"name":"a","level":1},{"id":3,"pid":1,"name":"c","level":2},{"id":7,"pid":3,"name":"g","level":3},
     * {"id":2,"pid":0,"name":"b","level":1},{"id":4,"pid":2,"name":"d","level":2},{"id":5,"pid":4,"name":"e","level":3},
     * {"id":6,"pid":5,"name":"f","level":4}]
     * @author 段誉(2021/4/12 16:44)
     */
    public static function multilevel_linear_sort($data, $symbol = '', $name = 'name', $id_name = 'id', $parent_id_name = 'pid', $level = 1, $parent_id = 0)
    {
        $result = [];
        $this_symbol = '';
        for ($i = 0; $i < $level; $i++) {
            $this_symbol .= $symbol;
        }
        foreach ($data as $key => $row) {
            if ($row['pid'] == $parent_id) {
                $row['level'] = $level;
                $row[$name] = $this_symbol . $row['name'];
                $result[] = $row;
                $pid = $row['id'];
                unset($data[$key]);
                $child_data = self::multilevel_linear_sort($data, $symbol, $name, $id_name, $parent_id_name, ($level + 1), $pid);
                foreach ($child_data as $child_row) {
                    $result[] = $child_row;
                }
            }
        }
        return $result;
    }



    /**
     * User: 意象信息科技 lr
     * Desc: 表单多维数据转换
     * 例：
     * 转换前：{"x":0,"a":[1,2,3],"b":[11,22,33],"c":[111,222,3333,444],"d":[1111,2222,3333]}
     * 转换为：[{"a":1,"b":11,"c":111,"d":1111},{"a":2,"b":22,"c":222,"d":2222},{"a":3,"b":33,"c":3333,"d":3333}]
     * @param $arr array 表单二维数组
     * @param $fill boolean fill为false，返回数据长度取最短，反之取最长，空值自动补充
     * @return array
     */
    public static function form_to_linear($arr, $fill = false)
    {
        $keys = [];
        $count = $fill ? 0 : PHP_INT_MAX;
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $keys[] = $k;
                $count = $fill ? max($count, count($v)) : min($count, count($v));
            }
        }
        if (empty($keys)) {
            return [];
        }
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            foreach ($keys as $v) {
                $data[$i][$v] = isset($arr[$v][$i]) ? $arr[$v][$i] : null;
            }
        }
        return $data;
    }


}