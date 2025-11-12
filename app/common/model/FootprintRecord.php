<?php


namespace app\common\model;


use app\common\basics\Models;
use app\common\enum\FootprintEnum;
use Exception;

/**
 * 足迹记录模型
 * Class FootprintRecord
 * @package app\common\model
 */
class FootprintRecord extends Models
{
    /**
     * Notes: 关联用户模型
     * @author 张无忌(2020/12/17 11:51)
     */
    public function user()
    {
        return $this->hasOne('app\common\model\user\User', 'id', 'user_id')
            ->field(['id', 'nickname', 'avatar']);
    }

    /**
     * @Notes: 获取器-转换时间
     * @Author: 张无忌
     * @param $value
     * @param $data
     * @return string
     */
    public function getTimeAttr($value, $data)
    {
        unset($value);
        // 足迹记录时间
        $create_time = strtotime($data['create_time']);
        // 一小时前时间戳
        $an_hour_ago = strtotime("-1 hour");

        // 小于1小时内显示xx分钟, 否则显示多少个小时
        if ($create_time > $an_hour_ago) {
            $minute = intval((time() - $create_time) / 60);

            return $minute <= 1 ? '刚刚' : strval($minute).'分钟前';
        } else {
            return '1小时前';
        }
    }

    /**
     * Notes: 获取30分钟内容的足迹
     * @param $data
     * @return array|bool
     * @author 张无忌(2020/12/16 18:17)
     */
    public static function getFootPrintOneHourInner($data)
    {
        try {
            // 一小时前时间戳
            $an_hour_ago = strtotime("-1 hour");
            // 30分钟前时间戳
            $half_an_hour_ago = $an_hour_ago + 1800;
            // 当前时间戳
            $current_time = time();

            $where = [
                ['create_time', '>', $half_an_hour_ago],
                ['create_time', '<', $current_time]
            ];
            if ($data['type']) {
                $where[] = ['user_id', '=', (int)$data['user_id']];
                $where[] = ['type', '=', (int)$data['type']];
            }

            // 进入商城
            if ($data['type'] === FootprintEnum::ENTER_MALL) {
                $where[] = ['foreign_id', '=', 0];
            }

            // 如果是浏览器商品
            if ($data['type'] === FootprintEnum::BROWSE_GOODS) {
                $where[] = ['foreign_id', '=', (int)$data['foreign_id']];
            }

            $model = new self;
            return $model->field(true)->where($where)->find();

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @Notes: 增加足迹
     * @Author: 张无忌
     * @param $data
     * @param $tpl
     * @return bool
     * @throws \think\Exception
     */
    public static function add($data, $tpl)
    {
        try {
            self::create([
                'type'        => $data['type'],
                'user_id'     => $data['user_id'],
                'foreign_id'  => empty($data['foreign_id']) ? 0 : $data['foreign_id'],
                'template'    => $tpl,
                'create_time' => time(),
            ]);

            return true;
        } catch (\Exception $e) {
            throw new \think\Exception($e->getMessage());
        }
    }
}