<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\logic\AgentLogic;
use app\admin\validate\AgentValidate;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\common\server\ConfigServer;
use think\exception\ValidateException;
use think\facade\View;

class Agent extends AdminBase
{
    /**
     * Notes: 列表
     * @return string|\think\response\Json
     * @author 段誉
     * @date 2024/01/01
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $result = AgentLogic::lists($get);
            return JsonServer::success('', $result);
        }
        return view();
    }

    /**
     * Notes: 添加
     * @return string|\think\response\Json
     * @author 段誉
     * @date 2024/01/01
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            try {
                validate(AgentValidate::class)->scene('add')->check($post);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }

            $result = AgentLogic::add($post);
            if ($result) {
                return JsonServer::success('添加成功');
            }
            return JsonServer::error('添加失败');
        }
        return view();
    }

    /**
     * Notes: 编辑
     * @return string|\think\response\Json
     * @author 段誉
     * @date 2024/01/01
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            try {
                validate(AgentValidate::class)->scene('edit')->check($post);
            } catch (ValidateException $e) {
                return JsonServer::error($e->getMessage());
            }

            $result = AgentLogic::edit($post);
            if ($result) {
                return JsonServer::success('编辑成功');
            }
            return JsonServer::error('编辑失败');
        }

        $id = $this->request->get('id', 0, 'intval');
        $detail = AgentLogic::detail($id);
        return view('', ['detail' => $detail]);
    }

    /**
     * Notes: 删除
     * @return \think\response\Json
     * @author 段誉
     * @date 2024/01/01
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (empty($post['ids'])) {
                return JsonServer::error('请选择要删除的数据');
            }
            AgentLogic::del($post['ids']);
            return JsonServer::success('删除成功');
        }
    }

    /**
     * Notes: 设置状态
     * @return \think\response\Json
     * @author 段誉
     * @date 2024/01/01
     */
    public function status()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            if (empty($post['ids'])) {
                return JsonServer::error('请选择要操作的数据');
            }
            AgentLogic::setStatus($post);
            return JsonServer::success('操作成功');
        }
    }

    /**
     * Notes: 代理设置
     * @return string|\think\response\Json
     * @author 段誉
     * @date 2024/01/01
     */
    public function setting()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            
            // 保存让利规则配置
            $rules = [
                [
                    'min_discount' => 3,
                    'max_discount' => 5,
                    'commission_rate' => $post['rule1_commission'] ?? 1
                ],
                [
                    'min_discount' => 6,
                    'max_discount' => 10,
                    'commission_rate' => $post['rule2_commission'] ?? 2
                ],
                [
                    'min_discount' => 11,
                    'max_discount' => 15,
                    'commission_rate' => $post['rule3_commission'] ?? 3
                ],
                [
                    'min_discount' => 16,
                    'max_discount' => 30,
                    'commission_rate' => $post['rule4_commission'] ?? 5
                ]
            ];
            
            ConfigServer::set('agent', 'discount_commission_rules', $rules);
            
            // 保存代理收益规则配置
            ConfigServer::set('agent', 'city_agent_rate', $post['city_agent_rate'] ?? 4); // 市级代理收益比例
            ConfigServer::set('agent', 'district_agent_rate', $post['district_agent_rate'] ?? 3); // 区级代理收益比例
            ConfigServer::set('agent', 'service_rate', $post['service_rate'] ?? 13); // 服务商收益比例
            ConfigServer::set('agent', 'service_bonus_min', $post['service_bonus_min'] ?? 5); // 服务商管理奖金最小比例
            ConfigServer::set('agent', 'service_bonus_max', $post['service_bonus_max'] ?? 10); // 服务商管理奖金最大比例
            ConfigServer::set('agent', 'promoter_rate', $post['promoter_rate'] ?? 8); // 推广商收益比例
            ConfigServer::set('agent', 'salesman_rate', $post['salesman_rate'] ?? 3); // 业务员收益比例
            ConfigServer::set('agent', 'salesman_upgrade_count', $post['salesman_upgrade_count'] ?? 10); // 业务员升级推广商条件（商家数量）
            
            return JsonServer::success('设置成功');
        }
        
        // 获取配置
        $rules = ConfigServer::get('agent', 'discount_commission_rules', [
            [
                'min_discount' => 3,
                'max_discount' => 5,
                'commission_rate' => 1
            ],
            [
                'min_discount' => 6,
                'max_discount' => 10,
                'commission_rate' => 2
            ],
            [
                'min_discount' => 11,
                'max_discount' => 15,
                'commission_rate' => 3
            ],
            [
                'min_discount' => 16,
                'max_discount' => 30,
                'commission_rate' => 5
            ]
        ]);
        
        // 提取各规则的收益比例，方便视图使用
        $rule1 = isset($rules[0]['commission_rate']) ? $rules[0]['commission_rate'] : 1;
        $rule2 = isset($rules[1]['commission_rate']) ? $rules[1]['commission_rate'] : 2;
        $rule3 = isset($rules[2]['commission_rate']) ? $rules[2]['commission_rate'] : 3;
        $rule4 = isset($rules[3]['commission_rate']) ? $rules[3]['commission_rate'] : 5;
        
        // 获取代理收益规则配置
        $city_agent_rate = ConfigServer::get('agent', 'city_agent_rate', 4);
        $district_agent_rate = ConfigServer::get('agent', 'district_agent_rate', 3);
        $service_rate = ConfigServer::get('agent', 'service_rate', 13);
        $service_bonus_min = ConfigServer::get('agent', 'service_bonus_min', 5);
        $service_bonus_max = ConfigServer::get('agent', 'service_bonus_max', 10);
        $promoter_rate = ConfigServer::get('agent', 'promoter_rate', 8);
        $salesman_rate = ConfigServer::get('agent', 'salesman_rate', 3);
        $salesman_upgrade_count = ConfigServer::get('agent', 'salesman_upgrade_count', 10);
        
        return view('', [
            'rules' => $rules,
            'rule1' => $rule1,
            'rule2' => $rule2,
            'rule3' => $rule3,
            'rule4' => $rule4,
            'city_agent_rate' => $city_agent_rate,
            'district_agent_rate' => $district_agent_rate,
            'service_rate' => $service_rate,
            'service_bonus_min' => $service_bonus_min,
            'service_bonus_max' => $service_bonus_max,
            'promoter_rate' => $promoter_rate,
            'salesman_rate' => $salesman_rate,
            'salesman_upgrade_count' => $salesman_upgrade_count
        ]);
    }
}
