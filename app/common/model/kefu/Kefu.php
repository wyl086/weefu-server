<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\model\kefu;


use app\common\basics\Models;
use app\common\server\UrlServer;

/**
 * 客服模型
 * Class kefu
 * @package app\common\model
 */
class Kefu extends Models
{

    protected $name = 'kefu';


    public function getAvatarAttr($value)
    {
        if (empty($value)) {
            return config('default.kefu_avatar');
        }
        return $value;
    }


    /**
     * @notes 添加客服
     * @param array $data
     * @param int $shop_id
     * @return Kefu|\think\Model
     * @author 段誉
     * @date 2021/11/26 17:41
     */
    public function insertKefu(array $data = [], int $shop_id = 0)
    {
        return self::create([
            'shop_id' => $shop_id,
            'admin_id' => $data['admin_id'],
            'nickname' => $data['nickname'],
            'avatar' => UrlServer::setFileUrl($data['avatar']),
            'disable' => $data['disable'],
            'sort' => $data['sort'] ?? 1,
            'create_time' => time(),
        ]);
    }


    /**
     * @notes 更新客服信息
     * @param array $data
     * @param int $shop_id
     * @return Kefu
     * @author 段誉
     * @date 2021/11/27 10:59
     */
    public function updateKefuData(int $id = 0, int $shop_id = 0, array $data = [])
    {
        return self::update($data, ['id' => $id, 'shop_id' => $shop_id, 'del' => 0]);
    }


    /**
     * @notes 编辑客服
     * @param int $id
     * @param array $params
     * @param int $shop_id
     * @return Kefu
     * @author 段誉
     * @date 2021/11/27 11:08
     */
    public function updateKefu(int $id, array $params = [], int $shop_id = 0)
    {
        $data = [
            'avatar' => UrlServer::setFileUrl($params['avatar']),
            'nickname' => $params['nickname'],
            'sort' => $params['sort'] ?? 1,
            'disable' => $params['disable'],
            'update_time' => time(),
        ];
        return $this->updateKefuData($id, $shop_id, $data);
    }


    /**
     * @notes 删除客服
     * @param int $id
     * @param int $shop_id
     * @return Kefu
     * @author 段誉
     * @date 2021/11/27 10:47
     */
    public function delKefu(int $id = 0, int $shop_id = 0)
    {
        $data = [
            'del' => 1,
            'update_time' => time()
        ];
        return $this->updateKefuData($id, $shop_id, $data);
    }


    /**
     * @notes 更新状态
     * @param int $id
     * @param int $status
     * @param int $shop_id
     * @return Kefu
     * @author 段誉
     * @date 2021/11/27 11:09
     */
    public function updateStatus(int $id = 0, int $status = 0, int $shop_id = 0)
    {
        $data = [
            'disable' => $status,
            'update_time' => time(),
        ];
        return $this->updateKefuData($id, $shop_id, $data);
    }

}