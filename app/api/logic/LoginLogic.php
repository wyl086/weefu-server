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

use app\admin\logic\user\LevelLogic;
use app\api\server\UserServer;
use app\common\basics\Logic;
use app\common\enum\ClientEnum;
use app\common\server\WeChatServer;
use app\common\server\ConfigServer;
use app\common\model\Client_;
use app\common\model\user\User;
use app\common\model\user\UserAuth;
use app\common\model\Session as SessionModel;
use app\common\model\Agent;
use EasyWeChat\Factory;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;
use app\api\cache\TokenCache;
use app\common\logic\AccountLogLogic;
use app\common\model\AccountLog;
use app\common\server\UrlServer;
use think\Exception;
use Requests;


class LoginLogic extends Logic
{
    /**
     * Notes: 旧用户登录
     * @param $post
     * @author 段誉(2021/4/19 16:57)
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function silentLogin($post)
    {
        try {
            //通过code获取微信 openid
            $response = self::getWechatResByCode($post);
            //通过获取到的openID或unionid获取当前 系统 用户id
            $user_id = self::getUserByWechatResponse($response);
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }

        if (empty($user_id)) {
            //系统中没有用户-调用authlogin接口生成新用户
            return [];
        } else {
            $user_info = UserServer::updateUser($response, Client_::mnp, $user_id);
        }

        //验证用户信息
        $check_res = self::checkUserInfo($user_info);
        if (true !== $check_res) {
            self::$error = $check_res;
            return  false;
        }

        //创建会话
        $user_info['token'] = self::createSession($user_info['id'], Client_::mnp);

        unset($user_info['id'], $user_info['disable']);

        return $user_info->toArray();
    }

    /**
     * Notes: 新用户登录
     * @param $post
     * @return array|false
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     *@author 段誉(2021/4/19 16:57)
     */
    public static function authLogin($post)
    {
        try {
            //通过code获取微信 openid
            $response = self::getWechatResByCode($post);
            $response['headimgurl'] = $post['headimgurl'] ?? '';
            $response['nickname'] = $post['nickname'] ?? '';
            //通过获取到的openID或unionid获取当前 系统 用户id
            $user_id = self::getUserByWechatResponse($response);
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }

        if (empty($user_id)) {
            $user_info = UserServer::createUser($response, Client_::mnp);
        } else {
            $user_info = UserServer::updateUser($response, Client_::mnp, $user_id);
        }

        //验证用户信息
        $check_res = self::checkUserInfo($user_info);
        if (true !== $check_res) {
            self::$error = $check_res;
            return false;
        }

        //创建会话
        $user_info['token'] = self::createSession($user_info['id'], Client_::mnp);

        unset($user_info['id'], $user_info['disable']);

        return $user_info->toArray();
    }

    /**
     * Notes: 根据code 获取微信信息(openid, unionid)
     * @param $post
     * @author 段誉(2021/4/19 16:52)
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function getWechatResByCode($post)
    {
        $config = WeChatServer::getMnpConfig();
        $app = Factory::miniProgram($config);
        $response = $app->auth->session($post['code']);
        if (empty($response['openid'])) {
            throw new \think\Exception('获取openID失败');
        }

        return $response;
    }
    
    public static function getWechatOaResByCode($post)
    {
        $config = WeChatServer::getOaConfig();
        $app = Factory::officialAccount($config);
        $response = $app
            ->oauth
            ->scopes(['snsapi_userinfo'])
            ->getAccessToken($post['code']);
        
        if (empty($response['openid'])) {
            throw new \think\Exception('获取openID失败');
        }
        
        return $response;
    }

    /**
     * Notes: 根据微信返回信息查询当前用户id
     * @param $response
     * @author 段誉(2021/4/19 16:52)
     * @return mixed
     */
    public static function getUserByWechatResponse($response)
    {
        $user_id = UserAuth::alias('au')
            ->join('user u', 'au.user_id=u.id')
            ->where(['u.del' => 0])
            ->where(function ($query) use ($response) {
                $query->whereOr(['au.openid' => $response['openid']]);
                if(isset($response['unionid']) && !empty($response['unionid'])){
                    $query->whereOr(['au.unionid' => $response['unionid']]);
                }
            })
            ->value('user_id');

        return $user_id;
    }


    /**
     * Notes: 检查用户信息
     * @param $user_info
     * @author 段誉(2021/4/19 16:54)
     * @return bool|string
     */
    public static function checkUserInfo($user_info)
    {
        if (empty($user_info)) {
            return '登录失败:user';
        }

        if ($user_info['disable']) {
            return '该用户被禁用';
        }

        return true;
    }

    /**
     * 创建会话
     * @param $user_id
     * @param $client
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function createSession($user_id, $client)
    {
        //清除之前缓存
        $token = SessionModel::where(['user_id' => $user_id, 'client' => $client])
            ->value('token');
        if($token) {
            Cache::delete($token);
        }

        $result = SessionModel::where(['user_id' => $user_id, 'client' => $client])
            ->findOrEmpty();

        $time = time();
        $expire_time = $time + Config::get('project.token_expire_time');
        // 新token
        $token = md5($user_id . $client . $time);
        $data = [
            'user_id' => $user_id,
            'token' => $token,
            'client' => $client,
            'update_time' => $time,
            'expire_time' => $expire_time,
        ];

        if ($result->isEmpty()) {
            SessionModel::create($data);
        } else {
            SessionModel::where(['user_id' => $user_id, 'client' => $client])
                ->update($data);
        }

        //更新登录信息
        $login_ip = $ip = request()->ip();
        User::where(['id' => $user_id])
            ->update(['login_time' => $time, 'login_ip' => $login_ip]);

        // 获取最新的用户信息
        $user_info = User::alias('u')
            ->join('session s', 'u.id=s.user_id')
            ->where(['s.token' => $token])
            ->field('u.*,s.token,s.client')
            ->find();
        $user_info = $user_info ? $user_info->toArray() : [];

        //创建新的缓存
        $ttl = 0 + Config::get('project.token_expire_time');
        Cache::set($token, $user_info, $ttl);

        return $token;
    }

    public static function register($post)
    {
        Db::startTrans();
        try{
            $time = time();
            $salt = substr(md5($time . $post['mobile']), 0, 4);//随机4位密码盐
            $password = create_password($post['password'], $salt);//生成密码
            $user_data = [
                'avatar'        => ConfigServer::get('website', 'user_image'),
                'sn'            => create_user_sn(),
                'mobile'        => $post['mobile'],
                'salt'          => $salt,
                'password'      => $password,
                'create_time'   => $time,
                'distribution_code' => generate_invite_code(),//分销邀请码
                'is_distribution' => DistributionLogic::isDistributionMember(),//是否为分销会员
                'client' => $post['client']
            ];

            $user_data['nickname'] = '用户'.$user_data['sn'];

            $user = User::create($user_data);

            $token = self::createSession($user->id, $post['client']);

            //生成会员分销扩展表
            DistributionLogic::createUserDistribution($user->id);

            // 生成分销基础信息表
            \app\common\logic\DistributionLogic::add($user->id);

            //注册赠送
            self::registerAward($user->id);

            // 同步创建代理数据
            self::syncAgentData($user->id, $post['mobile']);

            Db::commit();
            return ['token' => $token];
        }catch(\Exception $e){
            Db::rollback();
            self::$error = $e->getMessage();
            return false;
        }
    }

    public static function registerAward($user_id){
        // $register_award_integral_status = ConfigServer::get('marketing','register_award_integral_status',0);
        $register_award_coupon_status = ConfigServer::get('marketing','register_award_coupon_status',0);
        //赠送积分
        // if($register_award_integral_status){
        //     $register_award_integral = ConfigServer::get('marketing','register_award_integral',0);
        //     //赠送的积分
        //     if($register_award_integral > 0){
        //         $user = User::findOrEmpty($user_id);
        //         $user->user_integral += $register_award_integral;
        //         $user->save();
        //         AccountLogLogic::AccountRecord($user_id,$register_award_integral,1,AccountLog::register_add_integral,'');
        //     }
        // }
        //注册账号，首次进入首页时领取优惠券
        $register_award_coupon = ConfigServer::get('marketing','register_award_coupon','');
        if($register_award_coupon_status && $register_award_coupon){
            Cache::tag('register_coupon')->set('register_coupon_'.$user_id,$register_award_coupon);
        }
        // 赠送成长值
        $register_growth = ConfigServer::get('register', 'growth', 0);
        if($register_growth > 0) {
            $user = User::findOrEmpty($user_id);
            $user->user_growth += $register_growth;
            $user->save();
            AccountLogLogic::AccountRecord($user_id,$register_growth,1,AccountLog::register_give_growth,'');
            // 更新用户会员等级
            LevelLogic::updateUserLevel([$user]);
        }
    }

    /**
     * 同步创建代理数据
     * @param int $user_id 用户ID
     * @param string $mobile 手机号码
     * @return bool
     */
    public static function syncAgentData($user_id, $mobile)
    {
        try {
            // 检查该手机号是否已在代理表中存在
            $existAgent = Agent::where([
                'mobile' => $mobile,
                'del' => 0
            ])->findOrEmpty();

            // 如果已存在，则不重复添加
            if (!$existAgent->isEmpty()) {
                return true;
            }

            // 创建代理数据 - 用户端注册，source=2
            $agent = new Agent();
            $agent->pid = 1; // 推荐人ID，默认1
            $agent->invite_code = generate_agent_invite_code(); // 生成全局唯一邀请码
            $agent->source = 2; // 来源：2-用户（用户端注册）
            $agent->source_id = $user_id; // 用户ID
            $agent->mobile = $mobile;
            $agent->province_id = 0;
            $agent->city_id = 0;
            $agent->district_id = 0;
            $agent->is_city_agent = 0;
            $agent->is_district_agent = 0;
            $agent->is_service = 0;
            $agent->is_promoter = 0;
            $agent->status = 1; // 默认启用
            $agent->remark = '用户注册自动创建';
            $agent->create_time = time();
            $agent->update_time = time();
            $agent->save();

            return true;
        } catch (\Exception $e) {
            // 记录错误但不影响注册流程
            \think\facade\Log::error('同步代理数据失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 手机号密码登录
     */
    public static function mpLogin($post)
    {
        $user = User::field(['id', 'nickname', 'avatar', 'level', 'disable', 'distribution_code'])
            ->where(['mobile' => $post['mobile']])
            ->findOrEmpty()->toArray();
        $user['token'] = self::createSession($user['id'], $post['client']);
        if (empty($user['avatar'])) {
            $user['avatar'] = UrlServer::getFileUrl(ConfigServer::get('website', 'user_image'));
        } else {
            $user['avatar'] = UrlServer::getFileUrl($user['avatar']);
        }
        return $user;
    }

    /**
     * 获取code的url
     * @param $url
     * @return string
     */
    public static function codeUrl($url)
    {
        $config = WeChatServer::getOaConfig();
        $app = Factory::officialAccount($config);
        $response = $app
            ->oauth
            ->scopes(['snsapi_userinfo'])
            ->redirect($url)
            ->getTargetUrl();
        return $response;
    }

    /***
     * Desc: 微信公众号登录
     * @param $post
     * @return array|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function oaLogin($post)
    {
        //微信调用
        try {
            $config = WeChatServer::getOaConfig();
            $app = Factory::officialAccount($config);
            $response = $app
                ->oauth
                ->scopes(['snsapi_userinfo'])
                ->getAccessToken($post['code']);
            if (!isset($response['openid']) || empty($response['openid'])) {
                throw new Exception();
            }
            $user = $app->oauth->user($response);
            $user = $user->getOriginal();
            } catch (Exception $e) {
                return $e->getMessage();
            }
        //添加或更新用户
        $user_id = UserAuth::alias('au')
            ->join('user u', 'au.user_id=u.id')
            ->where(['u.del' => 0])
            ->where(function ($query) use ($user) {
                $query->whereOr(['au.openid' => $user['openid']]);
                if(isset($user['unionid']) && !empty($user['unionid'])){
                    $query->whereOr(['au.unionid' => $user['unionid']]);
                }
            })
            ->value('user_id');

        if (empty($user_id)) {
            $user_info = UserServer::createUser($user, Client_::oa);
        } else {
            $user_info = UserServer::updateUser($user, Client_::oa, $user_id);
        }

        if (empty($user_info)) {
            return '登录失败:user';
        }

        if ($user_info['disable']) {
            return '该用户被禁用';
        }

        //创建会话
        $user_info['token'] = self::createSession($user_info['id'], Client_::oa);


        unset($user_info['id']);
        unset($user_info['disable']);
        return $user_info->toArray();
//        return $user_info;

    }

    /***
     * app微信登录
     * @param $post
     * @return array|\PDOStatement|string|\think\Model|null
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function uinAppLogin($post)
    {
        //微信调用
        try {
            if (empty($post['openid']) || empty($post['access_token']) || empty($post['client'])){
                throw new Exception('参数缺失');
            }

            //sdk不支持app登录，直接调用微信接口
            $requests = Requests::get('https://api.weixin.qq.com/sns/userinfo?openid=' . 'openid=' . $post['openid'] . '&access_token=' . $post['access_token']);
            $user = json_decode($requests->body, true);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        //添加或更新用户
        $user_id = UserAuth::alias('au')->join('user u', 'au.user_id=u.id')
            ->where(['u.del' => 0])
            ->where(function ($query) use ($user) {
                $query->whereOr(['au.openid' => $user['openid']])
                    ->whereOr(['au.unionid' => $user['unionid']]);
            })
            ->value('user_id');

        if (empty($user_id)) {
            $user_info = UserServer::createUser($user, $post['client']);
        } else {
            $user_info = UserServer::updateUser($user, $post['client'], $user_id);
        }

        if (empty($user_info)) {
            return '登录失败:user';
        }

        if ($user_info['disable']) {
            return '该用户被禁用';
        }

        //创建会话
        $user_info['token'] = self::createSession($user_info['id'], $post['client']);

        unset($user_info['id']);
        unset($user_info['disable']);
        return $user_info;
    }

    //手机号密码登录
    public static function login($post)
    {
        $user_info = User::field(['id', 'nickname', 'avatar', 'level', 'disable', 'distribution_code'])
            ->where(['account|mobile' => $post['mobile']])
            ->find()->toArray();
        $user_info['token'] = self::createSession($user_info['id'], $post['client']);
        if (empty($user_info['avatar'])) {
            $user_info['avatar'] = UrlServer::getFileUrl(ConfigServer::get('website', 'user_image'));
        } else {
            $user_info['avatar'] = UrlServer::getFileUrl($user_info['avatar']);
        }
        return $user_info;
    }

    //退出登录
    public static function logout($user_id, $client)
    {
        return self::expirationSession($user_id, $client);
    }

    /**
     * 设置会话过期
     * @param $user_id
     * @param $client
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function expirationSession($user_id, $client)
    {
        $time = time();
        $token = Db::name('session')
            ->where(['user_id' => $user_id, 'client' => $client])
            ->value('token');
        $token_cache = new TokenCache($token);

        $token_cache->del();
        return Db::name('session')
            ->where(['user_id' => $user_id, 'client' => $client])
            ->update(['update_time' => $time, 'expire_time' => $time]);
    }


    /**
     * @notes PC扫码登录, 二维码链接
     * @return false|string
     * @author 段誉
     * @date 2021/10/29 11:47
     */
    public static function scanCode()
    {
        try {
            $config = WeChatServer::getOpWebConfig();
            $appid = $config['app_id'];
            $domain = request()->domain();
            $url = $domain.'/pc/account/login';
            $redirect_uri = UrlEncode($url);
            $state = MD5(time().rand(10000, 99999));
            cache($state, $state, 600); //缓存600
            $url = "https://open.weixin.qq.com/connect/qrconnect?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_login&state=$state#wechat_redirect";
            return $url;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }



    /**
     * @notes PC端扫码登录
     * @param $params
     * @return array|false
     * @author 段誉
     * @date 2021/10/29 18:00
     */
    public static function scanLogin($params)
    {
        try {
            //验证参数
            if (empty($params['code']) || empty($params['state'])) {
                throw new \Exception('参数缺失');
            }

            //验证state
            $state = cache($params['state']);
            if (empty($state)) {
                throw new \Exception('二维码已失效或不存在，请重新扫码');
            }

            $config = WeChatServer::getOpWebConfig();
            $appid = $config['app_id'];
            $secret = $config['secret'];

            //通过code获取access_token,openid,unionid
            $requests = Requests::get('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $secret . '&code=' . $params['code'] . '&grant_type=authorization_code');
            $user_auth = json_decode($requests->body, true);

            if (empty($user_auth['openid']) || empty($user_auth['access_token'])) {
                throw new \think\Exception('获取openID失败');
            }

            //获取用户信息
            $response = Requests::get('https://api.weixin.qq.com/sns/userinfo?access_token='. $user_auth['access_token'] . '&openid=' . $user_auth['openid']);
            $response = json_decode($response->body, true);

            //在系统中查找openid和unionid是否存在
            $user_id = self::getUserByWechatResponse($response);
            if (empty($user_id)) {
                $user_info = UserServer::createUser($response,ClientEnum::pc);
            } else {
                $user_info = UserServer::updateUser($response, ClientEnum::pc, $user_id);
            }

            //验证用户信息
            $check_res = self::checkUserInfo($user_info);
            if (true !== $check_res) {
                throw new \Exception($check_res);
            }

            //创建会话
            $user_info['token'] = self::createSession($user_info['id'], ClientEnum::pc);

            unset($user_info['id']);
            unset($user_info['disable']);
            return $user_info->toArray();

        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 更新用户头像昵称
     * @param $post
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DbException
     * @author ljj
     * @date 2023/2/2 6:23 下午
     */
    public static function updateUser($post,$user_id)
    {
//        $user = Db::name('user')->where(['id'=>$user_id])->find();
//        if ($user['is_new_user'] == 0) {
//            return self::dataError('非新用户无法使用此接口更新用户信息');
//        }
        Db::name('user')->where(['id'=>$user_id])->update(['nickname'=>$post['nickname'],'avatar'=>UrlServer::setFileUrl($post['avatar']),'is_new_user'=>0]);
        return true;
    }
    
    
    /**
     * @notes 小程序端绑定微信
     * @param array $params
     * @return false
     * @author lbzy
     * @datetime 2023-10-30 11:18:27
     */
    public function mnpAuthLogin(array $params)
    {
        try {
            //通过code获取微信openid
            $response = self::getWechatResByCode($params);
    
            $where = [
                [ 'openid', '=', $response['openid'] ],
            ];
            $userAuth = UserAuth::where($where)->findOrEmpty();
            if (isset($userAuth['id'])) {
                if ($userAuth['user_id'] == $params['user_id']) {
                    return true;
                }
                throw new \Exception('该微信已绑定其他用户');
            }
    
            UserAuth::create([
                'user_id'       => $params['user_id'],
                'openid'        => $response['openid'],
                'create_time'   => time(),
                'unionid'       => $response['unionid'] ?? '',
                'client'        => ClientEnum::mnp,
            ]);
            
            return true;
            
        } catch (\Exception  $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * @notes 公众号端绑定微信
     * @param array $params
     * @return false
     * @author lbzy
     * @datetime 2023-10-30 11:18:20
     */
    public function oaAuthLogin(array $params)
    {
        try {
            //通过code获取微信openid
            $response = self::getWechatOaResByCode($params);
        
            $where = [
                [ 'openid', '=', $response['openid'] ],
            ];
            $userAuth = UserAuth::where($where)->findOrEmpty();
            if (isset($userAuth['id'])) {
                if ($userAuth['user_id'] == $params['user_id']) {
                    return true;
                }
                throw new \Exception('该微信已绑定其他用户');
            }
        
            UserAuth::create([
                'user_id'       => $params['user_id'],
                'openid'        => $response['openid'],
                'create_time'   => time(),
                'unionid'       => $response['unionid'] ?? '',
                'client'        => ClientEnum::oa,
            ]);
        
            return true;
        
        } catch (\Exception  $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }
}
