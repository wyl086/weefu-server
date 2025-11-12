<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\common\server\sms;

use app\common\server\ConfigServer;

/**
 * 短信驱动
 * Class Driver
 * @package app\common\server\smsEngine
 */
class Driver
{
    protected $config;
    protected $smsEngine;
    protected $defaultEngine;
    protected $error;

    public function getError()
    {
        return $this->error;
    }


    public function __construct()
    {
        $this->initialize();
    }


    /**
     * @notes 初始化配置
     * @throws \Exception
     * @author 段誉
     * @date 2022/1/10 15:47
     */
    public function initialize()
    {
        $defaultEngine = ConfigServer::get('sms_driver', 'default', '');
        if (empty($defaultEngine)) {
            throw new \Exception('请开启短信配置');
        }
        $this->defaultEngine = $defaultEngine;

        $classSpace = __NAMESPACE__ . '\\engine\\' . ucfirst($defaultEngine.'Sms');
        if (!class_exists($classSpace)) {
            throw new \Exception('对应短信配置类不存在');
        }

        $engineConfig = ConfigServer::get('sms_engine', $defaultEngine, []);
        if (empty($engineConfig)) {
            throw new \Exception('请在后台设置好('.$defaultEngine.')的配置');
        }
        $this->smsEngine = new $classSpace($engineConfig);
    }


    /**
     * Notes: 发送短信
     * @param $mobile
     * @param $data
     * @author 段誉(2021/6/22 0:42)
     * @return bool
     */
    public function send($mobile, $data)
    {
        try{
            $res = $this->smsEngine
                ->setMobile($mobile)
                ->setTemplateId($data['template_id'])
                ->setTemplateParam($data['param'])
                ->send();

            if (false === $res) {
                throw new \Exception($this->smsEngine->getError());
            }
            return $res;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }



}