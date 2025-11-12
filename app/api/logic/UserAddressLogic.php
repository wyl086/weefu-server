<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\api\logic;


use app\common\model\DevRegion;
use app\common\model\user\UserAddress;
use app\common\server\AreaServer;
use think\facade\Db;
use think\Exception;

class UserAddressLogic
{
    /**
     * 获取用户地址信息
     * @param $user_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function infoUserAddress($user_id)
    {
        $UserAddress = new UserAddress();
        $info = $UserAddress
            ->where(['user_id' => $user_id, 'del' => 0])
            ->field('id,contact,telephone,province_id,city_id,district_id,address,is_default')
            ->select()
            ->toArray();
        foreach ($info as &$item) {
            $item['province'] = AreaServer::getAddress($item['province_id']);
            $item['city'] = AreaServer::getAddress($item['city_id']);
            $item['district'] = AreaServer::getAddress($item['district_id']);
        }
        return $info;
    }

    /**
     * 获取一条地址信息
     * @param $user_id
     * @param $get
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOneAddress($user_id, $get)
    {
        $UserAddress = new UserAddress();
        $info = $UserAddress
            ->where(['id' => (int)$get['id'], 'user_id' => $user_id, 'del' => 0])
            ->field('id,contact,telephone,province_id,city_id,district_id,address,is_default')
            ->find();

        $info['province'] = AreaServer::getAddress($info['province_id']);
        $info['city'] = AreaServer::getAddress($info['city_id']);
        $info['district'] = AreaServer::getAddress($info['district_id']);
        return $info;
    }

    /**
     * 获取默认地址
     * @param $user_id
     * @return array
     */
    public static function getDefaultAddress($user_id)
    {
        $UserAddress = new UserAddress();
        $info = $UserAddress
            ->where(['is_default' => 1, 'user_id' => $user_id, 'del' => 0])
            ->field('id,contact,telephone,province_id,city_id,district_id,address,is_default')
            ->findOrEmpty()->toArray();

        if (!$info) {
            return [];
        }

        $info['province'] = AreaServer::getAddress($info['province_id']);
        $info['city'] = AreaServer::getAddress($info['city_id']);
        $info['district'] = AreaServer::getAddress($info['district_id']);

        return $info;
    }

    /**
     * 设置默认地址
     * @param $user_id
     * @param $post
     * @return int|string
     */
    public static function setDefaultAddress($user_id, $post)
    {

        try {
            Db::startTrans();
            $UserAddress = new UserAddress();
            $UserAddress
                ->where(['del' => 0, 'user_id' => $user_id])
                ->update(['is_default' => 0]);

            $result = $UserAddress
                ->where(['id' => $post['id'], 'del' => 0, 'user_id' => $user_id])
                ->update(['is_default' => 1]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }

        return $result;
    }

    /**
     * 添加收货地址
     * @param $user_id
     * @param $post
     * @return int|string
     */
    public static function addUserAddress($user_id, $post)
    {
        try {
            Db::startTrans();
            $UserAddress = new UserAddress();
            if ($post['is_default'] == 1) {
                $UserAddress
                    ->where(['del' => 0, 'user_id' => $user_id])
                    ->update(['is_default' => 0]);
            } else {
                $is_first = $UserAddress
                    ->where(['del' => 0, 'user_id' => $user_id])
                    ->select();
                if (empty($is_first)) {
                    $post['is_default'] = 1;
                }
            }

            $data = [
                'user_id' => $user_id,
                'contact' => $post['contact'],
                'telephone' => $post['telephone'],
                'province_id' => $post['province_id'],
                'city_id' => $post['city_id'],
                'district_id' => $post['district_id'],
                'address' => $post['address'],
                'is_default' => $post['is_default'],
                'create_time' => time()
            ];

            $result = $UserAddress->insert($data);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }

        return $result;
    }

    /**
     * 编辑用户地址
     * @param $user_id
     * @param $post
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function editUserAddress($user_id, $post)
    {

        try {
            Db::startTrans();
            $UserAddress = new UserAddress();
            if ($post['is_default'] == 1) {
                $UserAddress->where(['del' => 0, 'user_id' => $user_id])
                    ->update(['is_default' => 0]);
            }

            $data = [
                'contact' => $post['contact'],
                'telephone' => $post['telephone'],
                'province_id' => $post['province_id'],
                'city_id' => $post['city_id'],
                'district_id' => $post['district_id'],
                'address' => $post['address'],
                'is_default' => $post['is_default'],
                'update_time' => time()
            ];

            $result = $UserAddress
                ->where(['id' => $post['id'], 'del' => 0, 'user_id' => $user_id])
                ->update($data);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }

        return $result;
    }

    /**
     * 删除用户地址
     * @param $user_id
     * @param $post
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function delUserAddress($user_id, $post)
    {

        $data = [
            'del' => 1,
            'update_time' => time()
        ];
        $UserAddress = new UserAddress();
        return $UserAddress
            ->where(['id' => $post['id'], 'del' => 0, 'user_id' => $user_id])
            ->update($data);
    }


    /**
     * 获取省市区id
     * @param $province
     * @param $city
     * @param $district
     * @return array
     */
    public static function handleRegion($province, $city, $district)
    {
        if (!$province || !$city || !$district) {
            return [];
        }
        $result = [];
        $result['province'] = self::handleRegionField($province, 1);
        if (!$result['province']) {
            return [];
        }
        $result['city'] = self::handleRegionField($city, 2);
        $result['district'] = self::handleRegionField($district, 3);

        return $result;
    }

    /**
     * 获取对应省,市,区的id
     * @param $keyword
     * @param int $level
     * @return mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function handleRegionField($keyword, $level = 1)
    {
        $data = '';
        $DevRegion = new DevRegion();
        $list = $DevRegion->where('level', $level)->select();
        foreach ($list as $k => $v) {
            if ($keyword == $v['name']) {
                $data = $v['id'];
            }
        }
        if (empty($data)) {
            foreach ($list as $k => $v) {
                if (strpos($v['name'], $keyword) !== false) {
                    $data = $v['id'];
                }
            }
            if (empty($data)) {
                foreach ($list as $v) {
                    if (strpos($keyword, $v['name']) !== false) {
                        $data = $v['id'];
                    }
                }
            }
        }
        return $data;
    }


    /**
     * User: 意象信息科技 mjf
     * Desc: 获取用户指定id的地址
     * @param $address
     * @param $user_id
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserAddressById($address, $user_id)
    {
        $UserAddress = new UserAddress();
        $info = $UserAddress
            ->where(['id' => $address, 'user_id' => $user_id, 'del' => 0])
            ->field('id,contact,telephone,province_id,city_id,district_id,address,is_default')
            ->find();

        if (!$info) {
            return [];
        }

        $info['province'] = AreaServer::getAddress($info['province_id']);
        $info['city'] = AreaServer::getAddress($info['city_id']);
        $info['district'] = AreaServer::getAddress($info['district_id']);

        return $info;
    }


}