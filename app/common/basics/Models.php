<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------


namespace app\common\basics;


use app\common\server\UrlServer;
use think\Model;

/**
 * 模型 基类
 * Class Models
 * @Author FZR
 * @package app\common\basics
 */
abstract class Models extends Model
{
    // 定义公共操作 如  删除  切换状态等
    
    /**
     * updateData 回调 记录update的条数
     * @var integer
     */
    public $_updateResult;
    
    final protected function checkResult($result) : void
    {
        $this->_updateResult = $result;
    }
    
    final function getUpdateResult() : int
    {
        return $this->_updateResult;
    }
    
    /**
     * NOTE: 修改器-图片转相对路径
     * @author: 张无忌
     * @param $value
     * @return mixed|string
     */
    public function setImageAttr($value)
    {
        return $value ? UrlServer::setFileUrl($value) : '';
    }

    /**
     * NOTE: 获取器-补全图片路径
     * @author: 张无忌
     * @param $value
     * @return string
     */
    public function getImageAttr($value,$data)
    {
        if(!$value && isset($data['goods_snap'])){
            return UrlServer::getFileUrl($data['goods_snap']['image']);
        }
        return $value ? UrlServer::getFileUrl($value) : '';
    }
    
    /**
     * @notes 统一处理用户nickname
     * @param $nickname
     * @return string
     * @author lbzy
     * @datetime 2023-09-06 10:35:17
     */
    function getNicknameAttr($nickname)
    {
        if (in_array(app('http')->getName(), [ 'admin', 'shop' ]) && request()->isAjax()) {
            $nickname = htmlspecialchars($nickname);
        }
    
        return $nickname;
        
    }
}