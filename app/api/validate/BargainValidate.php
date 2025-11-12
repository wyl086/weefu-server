<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\api\validate;

use app\common\model\bargain\BargainLaunch;
use think\facade\Db;
use app\common\basics\Validate;

/**
 * Class BargainValidate
 * @package app\api\validate
 */
class BargainValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'bargain_id' => 'require',
        'item_id' => 'require|checkGoods',
        'url' => 'require',
    ];
    protected $message = [
        'id.require' => '请选择砍价订单',
        'bargain_id.require' => '请选择活动',
        'item_id.require' => '请选择规格',
        'url.require' => '缺少参数',
    ];

    /**
     * @notes 砍价商品详情验证场景
     * @author suny
     * @date 2021/7/13 6:27 下午
     */
    public function sceneDetail()
    {

        $this->only(['bargain_id'])
            ->append('bargain_id', 'checkBargain');

    }

    /**
     * @notes 发起砍价验证
     * @author suny
     * @date 2021/7/13 6:27 下午
     */
    public function sceneSponsor()
    {

        $this->only(['bargain_id', 'item_id'])->append('bargain_id', 'checkBargain');
    }

    /**
     * @notes 砍价详情验证
     * @author suny
     * @date 2021/7/13 6:27 下午
     */
    public function sceneBargainDetail()
    {

        $this->only(['id']);
    }

    /**
     * @notes 分享验证
     * @author suny
     * @date 2021/7/13 6:27 下午
     */
    public function sceneShare()
    {

        $this->only(['id', 'url'])
            ->append('id', 'checkBargainLaunch');
    }

    /**
     * @notes 助力验证
     * @author suny
     * @date 2021/7/13 6:27 下午
     */
    public function sceneKnife()
    {

        $this->only(['id'])
            ->append('id', 'checkBnife');
    }

    /**
     * @notes 验证活动是否开启
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:28 下午
     */
    protected function checkBargain($value, $rule, $data)
    {

        $now = time();
        $bargain = Db::name('bargain')
            ->where([
                ['id', '=', $value],
                ['del', '=', 0],
                ['activity_start_time', '<', $now],
                ['activity_end_time', '>', $now],
                ['status', '=', 1]
            ])
            ->find();

        if (empty($bargain)) {
            return '该砍价活动已下架';
        }
        return true;

    }

    /**
     * @notes 验证商品库存
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @author suny
     * @date 2021/7/13 6:28 下午
     */
    protected function checkGoods($value, $rule, $data)
    {

        $stock = Db::name('goods_item')
            ->where(['id' => $value])
            ->value('stock');

        if ($stock < 1) {
            return '该商品库存不足';
        }
        return true;
    }

    /**
     * @notes 验证该砍价订单是否结束
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author suny
     * @date 2021/7/13 6:28 下午
     */
    protected function checkBargainLaunch($value, $rule, $data)
    {

        $bargain_launch = new BargainLaunch();
        $bargain_launch = $bargain_launch
            ->where(['id' => $value])
            ->find();
        if ($bargain_launch['launch_end_time'] <= time() || $bargain_launch['status'] !== 0) {
            return '该砍价已结束';
        }
        return true;
    }

    /**
     * @notes 验证该砍价订单是否可助力
     * @param $value
     * @param $rule
     * @param $data
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author suny
     * @date 2021/7/13 6:28 下午
     */
    protected function checkBnife($value, $rule, $data)
    {

        $bargain_launch = new BargainLaunch();
        $bargain_launch = $bargain_launch
            ->where(['id' => $value])
            ->find()->toarray();
        if (0 != $bargain_launch['status']) {
            return '该砍价已结束';
        }
        if ($bargain_launch['launch_end_time'] <= time()) {
            return '该砍价已结束';
        }
        if ($bargain_launch['user_id'] === $data['user_id']) {
            return '不能助力自己的砍价活动';
        }
        if ($bargain_launch['current_price'] < 0) {
            return '该砍价活动已成功';
        }
        //当前活动是砍到低价，且已经低于等于活动低价时，砍价成功
        if (1 == $bargain_launch['bargain_snap']['payment_where'] && $bargain_launch['current_price'] <= $bargain_launch['bargain_price']) {
            return '该砍价活动已成功';
        }
        $bargain_knife = Db::name('bargain_knife')
            ->where(['launch_id' => $value, 'user_id' => $data['user_id']])
            ->find();

        if ($bargain_knife) {
            return '您已助力过了';
        }


        return true;
    }


}