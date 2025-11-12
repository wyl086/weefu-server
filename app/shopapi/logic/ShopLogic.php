<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | 开源版本可自由商用，可去除界面版权logo
// | 商业版本务必购买商业授权，以免引起法律纠纷
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop团队 版权所有 拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshopTeam
// +----------------------------------------------------------------------
namespace app\shopapi\logic;
use app\admin\controller\activity_area\Area;
use app\common\enum\NoticeEnum;
use app\common\enum\WithdrawalEnum;
use app\common\model\shop\Shop;
use app\common\model\shop\ShopAccountLog;
use app\common\model\shop\ShopAdmin;
use app\common\model\shop\ShopBank;
use app\common\model\shop\ShopCategory;
use app\common\model\shop\ShopWithdrawal;
use app\common\server\AreaServer;
use app\common\server\ConfigServer;
use think\facade\Db;

/**
 * 商家逻辑层
 * Class ShopLogic
 * @package app\shopapi\logic
 */
class ShopLogic{

    /**
     * @notes 获取商家可提现余额
     * @param $shop_id
     * @return mixed
     * @author cjhao
     * @date 2021/11/10 16:15
     */
    public function getShopInfo(int $shop_id){
        $shop = Shop::where(['id'=>$shop_id])
            ->field("id,cid,name,logo,is_run,wallet,score,nickname,mobile,intro,
            run_start_time,run_end_time,weekdays,province_id,city_id,district_id,address,refund_address,open_invoice,spec_invoice")
            ->find()->toArray();
        
        $shop['run_start_time'] = date('H:i',$shop['run_start_time']);
        $shop['run_end_time'] = date('H:i',$shop['run_end_time']);


        $shop['province_name'] = '';
        $shop['city_name'] = '';
        $shop['district_name'] = '';

        $shop['province_id'] && $shop['province_name'] = AreaServer::getAddress($shop['province_id']);
        $shop['city_id'] && $shop['city_name'] = AreaServer::getAddress($shop['city_id']);
        $shop['district_id'] && $shop['district_name'] = AreaServer::getAddress($shop['district_id']);

        $shop['refund_address']['province_name'] = !empty($shop['refund_address']['province_id']) ? AreaServer::getAddress($shop['refund_address']['province_id']) : '';
        $shop['refund_address']['city_name'] = !empty($shop['refund_address']['city_id']) ? AreaServer::getAddress($shop['refund_address']['city_id']) : '';
        $shop['refund_address']['district_name'] = !empty($shop['refund_address']['district_id']) ? AreaServer::getAddress($shop['refund_address']['district_id']) : '';

        $shop['cate_name'] = ShopCategory::where('id', $shop['cid'])->value('name');

        return $shop;

    }

    /**
     * @notes 获取提现信息
     * @param int $shop_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author cjhao
     * @date 2021/11/10 16:30
     */
    public function getWithdrawInfo(int $shop_id){
        $wallet = Shop::where(['id'=>$shop_id])
            ->value("wallet");

        $min_withdrawal_money = ConfigServer::get('shop_withdrawal', 'min_withdrawal_money', 0);
        $max_withdrawal_money = ConfigServer::get('shop_withdrawal', 'max_withdrawal_money', 0);
        $withdrawal_service_charge = ConfigServer::get('shop_withdrawal', 'withdrawal_service_charge', 0);

        $bank_list = ShopBank::where(['shop_id'=>$shop_id,'del'=>0])
                        ->field('id,name,branch,nickname,account')
                        ->select()->toArray();
        return [
            'wallet'                    => $wallet,
            'min_withdrawal_money'      => $min_withdrawal_money,
            'max_withdrawal_money'      => $max_withdrawal_money,
            'withdrawal_service_charge' => $withdrawal_service_charge,
            'bank_list'                 => $bank_list,
        ];

    }

    /**
     * @notes 提现金额
     * @param array $post
     * @return bool|string
     * @author cjhao
     * @date 2021/11/10 16:56
     */
    public function withdraw(array $post){
        Db::startTrans();
        try {
            $shop_id = $post['shop_id'];
            // 1、获取提现条件
            $withdrawal_service_charge = ConfigServer::get('shop_withdrawal', 'withdrawal_service_charge', 0);

            // 2、获取商家信息
            $shop   = (new Shop())->findOrEmpty($shop_id)->toArray();

            // 4、获取商家提现手续费
            $poundage_amount   = 0;
            if ($withdrawal_service_charge > 0) {
                $proportion = $withdrawal_service_charge / 100;
                $poundage_amount = $post['money'] * $proportion;
                $poundage_amount = $poundage_amount <= 0 ? 0 : $poundage_amount;
            }

            // 5、创建申请记录
            $withdrawal = ShopWithdrawal::create([
                'sn'              => createSn('shop_withdrawal', 'sn'),
                'bank_id'         => $post['bank_id'],
                'shop_id'         => $shop_id,
                'apply_amount'    => floatval($post['money']),
                'left_amount'     => $post['money'] - $poundage_amount,
                'poundage_amount' => $poundage_amount,
                'poundage_ratio'  => $withdrawal_service_charge,
                'status'          => WithdrawalEnum::APPLY_STATUS
            ]);
            // 6、扣除商家可提现金额
            Shop::update([
                'wallet'      => ['dec', floatval($post['money'])],
                'update_time' => time()
            ], ['id' => $shop_id]);

            $left_amount =  Shop::where(['id' => $shop_id])->value('wallet');
            // 7、增加提现流水记录(待提现)
            $logType = ShopAccountLog::withdrawal_stay_money;
            ShopAccountLog::decData($shop_id, $logType, $post['money'], $left_amount, [
                'source_id' => $withdrawal['id'],
                'source_sn' => $withdrawal['sn'],
                'remark'    => '商家提现'
            ]);

            $platform_contacts = ConfigServer::get('website_platform', 'platform_mobile');
            if (!empty($platform_contacts)) {
                event('Notice', [
                    'scene' => NoticeEnum::SHOP_WITHDRAWAL_NOTICE_PLATFORM,
                    'mobile' => $platform_contacts,
                    'params' => [
                        'shop_withdrawal_sn' => $withdrawal['sn'],
                        'shop_name' => $shop['name'],
                    ]
                ]);
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @notes 提现记录
     * @param $shop_id
     * @param $page_no
     * @param $page_size
     * @return array
     * @throws \think\db\exception\DbException
     * @author cjhao
     * @date 2021/11/10 17:10
     */
    public function withdrawLog(int $shop_id,int $page_no,int $page_size){
        $lists = ShopWithdrawal::alias('SW')
            ->join('shop_account_log SCL','SW.sn = SCL.source_sn')
            ->where(['SW.shop_id'=>$shop_id,'source_type'=>[ShopAccountLog::withdrawal_stay_money,ShopAccountLog::withdrawal_dec_money,ShopAccountLog::withdrawal_fail_money]])
            ->field("SCL.id,SCL.change_amount,SCL.left_amount,status,SCL.create_time")
            ->paginate([
                'page'      => $page_no,
                'list_rows' => $page_size,
                'var_page' => 'page'
            ])->toArray();

        return ['count' => $lists['total'], 'lists' => $lists['data']];
    }


    /**
     * @notes 添加银行账户
     * @param $post
     * @return bool
     * @author cjhao
     * @date 2021/11/10 18:30
     */
    public function addBank(array $post){
        $shop_bank = new ShopBank();
        $shop_bank->shop_id     = $post['shop_id'];
        $shop_bank->name        = $post['name'];
        $shop_bank->branch      = $post['branch'];
        $shop_bank->nickname    = $post['nickname'];
        $shop_bank->account     = $post['account'];
        $shop_bank->save();
        return true;
    }

    /**
     * @notes 获取银行卡
     * @param int $id
     * @param int $shop_id
     * @return array
     * @author cjhao
     * @date 2021/11/11 15:46
     */
    public function getBank(int $id,int $shop_id){
        $shop_bank = ShopBank::where(['id'=>$id,'shop_id'=>$shop_id])
                    ->field('id,name,branch,nickname,account')
                    ->findOrEmpty()->toArray();
        return $shop_bank;
    }

    /**
     * @notes 编辑银行账户
     * @param $post
     * @return bool
     * @author cjhao
     * @date 2021/11/10 18:38
     */
    public function editBank(array $post){
        ShopBank::update([
            'name'     => $post['name'],
            'branch'   => $post['branch'],
            'nickname' => $post['nickname'],
            'account'  => $post['account'],
            'del'      => 0
        ], ['id'=>$post['id']]);
        return true;

    }

    /**
     * @notes 删除银行卡
     * @param $id
     * @param $shop_id
     * @return bool
     * @author cjhao
     * @date 2021/11/10 18:42
     */
    public function delBank(int $id,int $shop_id){
        ShopBank::where(['id'=>$id,'shop_id'=>$shop_id])->delete();
        return true;
    }

    /**
     * @notes 更新商家信息
     * @param array $post
     * @param int $shop_id
     * @return Shop
     * @author cjhao
     * @date 2021/11/11 11:34
     */
    public function shopSet(array $post,int $shop_id){
        if(isset($post['refund_address'])){
            $post['refund_address'] = json_encode($post['refund_address'],JSON_UNESCAPED_UNICODE);
        }
        return Shop::update($post,['id'=>$shop_id]);
    }


    /**
     * @notes 修改密码
     * @param $password
     * @param $admin_id
     * @param $shop_id
     * @return bool
     * @author cjhao
     * @date 2021/11/11 16:03
     */
    public function updatePassword(array $post,int $shop_id)
    {
        try {
            $admin = ShopAdmin::where(['id' => $post['admin_id'], 'shop_id' => $shop_id])->find();
            $admin->password = generatePassword($post['password'], $admin['salt']);
            $admin->save();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}