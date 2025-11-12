<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\admin\logic;

use app\common\basics\Logic;
use app\common\model\RechargeTemplate;
use think\facade\Db;
use app\common\server\ConfigServer;

class RechargeLogic extends Logic
{
    public static function templatelists(){
        $list = RechargeTemplate::where(['del'=>0])->order(['sort' => 'desc'])->select()->toArray();
        foreach ($list as &$item){
            $item['money'] && $item['money'] = '￥'.$item['money'];
            $item['give_money'] && $item['give_money'] = '￥'.$item['give_money'];
        }
        return $list;
    }

    public static function getRechargeConfig(){
        $config =  [
            'open_racharge'  => ConfigServer::get('recharge','open_racharge',0),
            'give_growth'    => ConfigServer::get('recharge', 'give_growth', 0),
            'min_money'      => ConfigServer::get('recharge', 'min_money', 0),
        ];
        return [$config];
    }

    public static function add($post){
        try{
            // 判断充值金额是否已存在
            $recharge_template = RechargeTemplate::where([
                'del' =>0,
                'money' => $post['money']
            ])->findOrEmpty();
            if(!$recharge_template->isEmpty()) {
                throw new \think\Exception('该充值金额的模板已存在');
            }
            $new = time();
            $add_data = [
                'money'         => $post['money'],
                'give_money'    => $post['give_money'],
                'sort'          => $post['sort'],
                'is_recommend'  => $post['is_recommend'],
                'create_time'   => $new,
                'update_time'   => $new,
            ];
            RechargeTemplate::create($add_data);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function changeTableValue($table,$pk_name,$pk_value,$field,$field_value){
        //允许修改的字段
        $allow_field = [
            'is_show','sort','status','is_new','is_best','is_like','is_recommend'
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


    public static function getRechargeTemplate($id){
        return Db::name('recharge_template')->where(['id'=>$id])->find();
    }

    public static function edit($post){
        try{
            // 判断充值金额是否已存在
            $recharge_template = RechargeTemplate::where([
                ['del', '=', 0],
                ['money', '=', $post['money']],
                ['id', '<>', $post['id']],
            ])->findOrEmpty();
            if(!$recharge_template->isEmpty()) {
                throw new \think\Exception('该充值金额的模板已存在');
            }
            $new = time();
            $update_data = [
                'id'            => $post['id'],
                'money'         => $post['money'],
                'give_money'    => $post['give_money'],
                'sort'          => $post['sort'],
                'is_recommend'  => $post['is_recommend'],
                'update_time'   => $new,
            ];
            RechargeTemplate::update($update_data);
            return true;
        }catch(\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function del($id){
        return Db::name('recharge_template')->where(['id'=>$id])->update(['update_time'=>time(),'del'=>1]);
    }

    public static function setRecharge($post){
        ConfigServer::set('recharge','open_racharge',$post['open_racharge']);
        ConfigServer::set('recharge','give_growth',$post['give_growth']);
        ConfigServer::set('recharge','min_money',$post['min_money']);
    }
}
