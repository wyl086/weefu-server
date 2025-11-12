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

use app\common\server\ConfigServer;

use app\common\server\UrlServer;
use think\facade\Db;
use think\Exception;

class ExpressLogic
{
    /**
     * 列表
     * @param $get
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function lists($get)
    {
        $where = [];
        $where[] = ['del', '=', '0'];

        if (isset($get['express_name']) && $get['express_name'] != '') {
            $where[] = ['name', 'like', '%' . trim($get['express_name']) . '%'];
        }

        $count = Db::name('express')
            ->where($where)
            ->count();

        $lists = Db::name('express')
            ->where($where)
            ->page($get['page'], $get['limit'])
            ->select()->toArray();

        foreach ($lists as $key => &$item) {
            $item['icon'] = UrlServer::getFileUrl($item['icon']);
        }
        return ['lists' => $lists, 'count' => $count];
    }


    /**
     * 添加
     * @param $post
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function addExpress($post)
    {
        $time = time();
        $data = [
            'name' => $post['name'],
            'icon' => clearDomain($post['poster']),
            'website' => $post['website'],
            'code' => $post['code'],
            'code100' => $post['code100'],
            'codebird' => $post['codebird'],
            'sort' => $post['sort'],
            'create_time' => $time
        ];
        $result = Db::name('express')->insert($data);
        return $result;
    }

    /**
     * 获取信息
     * @param $id
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function info($id)
    {
        $detail = Db::name('express')->where(['id' => $id, 'del' => 0])->find();
        $detail['abs_icon'] = UrlServer::getFileUrl($detail['icon']);
        return $detail;
    }


    /**
     * 编辑
     * @param $post
     * @return int|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function editExpress($post)
    {
        $data = [
            'name' => $post['name'],
            'icon' => UrlServer::setFileUrl($post['poster']),
            'website' => $post['website'],
            'code' => $post['code'],
            'code100' => $post['code100'],
            'codebird' => $post['codebird'],
            'sort' => $post['sort'],
            'update_time' => time()
        ];
        $result = Db::name('express')->where(['id' => $post['id']])->update($data);
        return $result;
    }


    /**
     * 删除
     * @param $delData
     * @return int|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function delExpress($delData)
    {
        $data = [
            'del' => 1,
            'update_time' => time(),
        ];
        return Db::name('express')->where(['del' => 0, 'id' => $delData])->update($data);
    }




    public static function getExpress()
    {
        $config = [
            'way' => ConfigServer::get('express', 'way', 'kd100'),
            'kd100_appkey' => ConfigServer::get('kd100', 'appkey', ''),
            'kd100_customer' => ConfigServer::get('kd100', 'appsecret', ''),
            'kdniao_appkey' => ConfigServer::get('kdniao', 'appkey', ''),
            'kdniao_ebussinessid' => ConfigServer::get('kdniao', 'appsecret', ''),
            'kdniao_type' => ConfigServer::get('kdniao', 'type', 'free'),
        ];
        return $config;
    }

    //快递100
    public static function kd100(){
        $config = [
            'appkey' => ConfigServer::get('kd100', 'appkey', ''),
            'appsecret' => ConfigServer::get('kd100', 'appsecret', ''),

        ];
        return $config;
    }
    //快递鸟
    public static function kdniao(){
        $res=[
            'appkey2' => ConfigServer::get('kdniao', 'appkey', ''),
            'appsecret2' => ConfigServer::get('kdniao', 'appsecret', ''),
        ];
        return $res;
    }
    //快递方式
    public static function way(){
        $way = ConfigServer::get('express', 'way', '');
        return $way;
    }

}
