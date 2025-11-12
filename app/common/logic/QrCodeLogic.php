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
use app\common\enum\ClientEnum;
use app\common\model\RechargeTemplate;
use app\common\server\ConfigServer;
use app\common\server\FileServer;
use app\common\server\storage\Driver as StorageDriver;
use app\common\server\UrlServer;
use app\common\server\WeChatServer;
use app\common\server\JsonServer;
use EasyWeChat\Kernel\Http\StreamResponse;
use Endroid\QrCode\QrCode;
use think\facade\Cache;
use think\Exception;
use EasyWeChat\Factory;
use think\facade\Log;
use think\facade\Request;

class QrCodeLogic
{
    //商品图片配置
    public function goodsShareConfig(){
        return [
            //会员头像
            'head_pic' => [
                'w' => 64, 'h' => 64, 'x' => 40, 'y' => 20,
            ],
            //会员昵称
            'nickname' => [
                'color' => '#555555', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 19, 'x' => 120, 'y' => 60,
            ],
            //标题
            'title' => [
                'color' => '#333333', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Bold.otf', 'font_size' => 20, 'w' => 360, 'x' => 40, 'y' => 785,
            ],
            //价格符号
            'price_symbol' => [
                'color' => '#FF2C3C', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 22, 'w' => 140, 'x' => 40, 'y' => 722,
            ],
            //商品价格
            'price' => [
                'color' => '#FF2C3C', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Bold.otf', 'font_size' => 32, 'w' => 140, 'x' => 64, 'y' => 722,
            ],
            //小数
            'decimal'=> [
                'color' => '#FF2C3C', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Bold.otf', 'font_size' => 22, 'w' => 140, 'x' => 114, 'y' => 722,
            ],
            //市场价
            'market_price_symbol' => [
                'color' => '#999999', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 22, 'w' => 140, 'x' => 142, 'y' => 722,
            ],
            //市场价符号
            'market_price' => [
                'color' => '#999999', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'w' => 140, 'x' => 168, 'y' => 722,
            ],
            //推广主图商品主图
            'main_pic' => [
                'w' => 560, 'h' => 560, 'x' => 40, 'y' => 100,
            ],
            //提醒长按扫码
            'notice' => [
                'color' => '#888888', 'font_face' =>  ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 18, 'x' => 432, 'y' => 895,
            ],
            //二维码
            'qr' => [
                'w' => 165,'h' => 165, 'x' => 436, 'y' => 700,
            ],
        ];
    }
    //生成商品分享图
    public function makeGoodsPoster($user,$goods,$url,$url_type){

        try {
            $save_dir = ROOT_PATH .'/uploads/qr_code/goods_share/';
            $background_img = ROOT_PATH .'/images/share/share_goods_bg.png';
            !file_exists($save_dir) && mkdir($save_dir, 0777, true);

            $cache_key = 'gid' . $goods['id'].'uid'.$user['id'].$url_type;
            $qr_src = md5($cache_key) . '.png';
            $poster_url = $save_dir . $qr_src;

            $base64 = Cache::get($cache_key);
            if (!empty($base64)) {
                $data = [
                    'code' => 1,
                    'msg' => '海报生成成功',
                    'show' => 0,
                    'data' => $base64
                ];
                return json($data);
            }


            $poster_config = self::goodsShareConfig();
            //生成二维码
            if($url_type == 'path'){
                $scene = 'id='.$goods['id'].'&invite_code='.$user['distribution_code'];
                $result = $this->makeMnpQrcode($scene,$url,$qr_src,$save_dir);
                if(true !== $result){
                    return JsonServer::error('微信配置错误：'.$result);
                }
            }else{

                $qrCode = new QrCode();
                $qrCode->setText($url);
                $qrCode->setSize(1000);
                $qrCode->setWriterByName('png');
                $qrCode->writeFile($poster_url);

            }
            $user_avatar =  UrlServer::getFileUrl($user['avatar']);

            //判断头像是否存在
            if(!check_file_exists($user_avatar)){
                //如果不存在，使用默认头像
                $user_avatar = ROOT_PATH.ConfigServer::get('website', 'user_image');
            }

            //默认商品主图
            $goods_image = UrlServer::getFileUrl($goods['image']);

            //判断是否有自定义分享海报图
            if($goods['poster']){
                $goods_image = UrlServer::getFileUrl($goods['poster']);
            }

            if(!check_file_exists($goods_image)){
                //如果不存在，使用默认商品主图
                $goods_image = ROOT_PATH.ConfigServer::get('website', 'goods_image');
            }

            $qr_code_logic = new QrCodeLogic();
            //获取背景图
            $share_background_img = imagecreatefromstring(file_get_contents($background_img));

            //合成头像
            $qr_code_logic->writeImg($share_background_img, $user_avatar, $poster_config['head_pic'],true);
            //合并商品主图
            $qr_code_logic->writeImg($share_background_img, $goods_image, $poster_config['main_pic'],false);

            //合成昵称
            $nickname = filterEmoji($user['nickname']);
            $nickname = '来自'.$nickname.'的分享';
            $qr_code_logic->writeText($share_background_img, $nickname, $poster_config['nickname']);

            //长按识别
            $notice = '长按识别二维码';
            $qr_code_logic->writeText($share_background_img, $notice, $poster_config['notice']);

            //合成价格
            $qr_code_logic->writeText($share_background_img, '￥', $poster_config['price_symbol']);
            $qr_code_logic->writeText($share_background_img, floatval($goods['min_price']), $poster_config['price']);

            //合成商品标题
            $goods_name = auto_adapt($poster_config['title']['font_size'], 0, $poster_config['title']['font_face'], $goods['name'], $poster_config['title']['w'],$poster_config['title']['y'],getimagesize($background_img));
            $qr_code_logic->writeText($share_background_img, $goods_name, $poster_config['title']);

            //合成二维码
            $qr_code_logic->writeImg($share_background_img, $poster_url, $poster_config['qr'],false);
            imagepng($share_background_img, $poster_url);
            if ($fp = fopen($poster_url, "rb", 0)) {
                $gambar = fread($fp, filesize($poster_url));
                fclose($fp);
                $base64 = chunk_split(base64_encode($gambar));
                $base64 = 'data:image/png;base64,' . $base64;
            }

            //删除文件
//            if (strstr($poster_url, $save_dir)) {
//                unlink($poster_url);
//            }
            Cache::set($cache_key, $base64, 3600);

            $data = [
                'code' => 1,
                'msg' => '海报生成成功',
                'show' => 0,
                'data' => $base64
            ];
            return json($data);

        }catch (Exception $e){
            return JsonServer::error('海报生成错误:' . $e->getMessage());
        }

    }


    //用户图片配置
    public function userShareConfig()
    {
        return [
            //会员头像
            'head_pic' => [
                'w' => 80, 'h' => 80, 'x' => 30, 'y' => 680,
            ],
            //会员昵称
            'nickname' => [
                'color' => '#333333', 'font_face' => ROOT_PATH.'/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'x' => 120, 'y' => 730,
            ],
            //标题
            'title' => [
                'color' => '#333333', 'font_face' => ROOT_PATH.'/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'w' => 360, 'x' => 30, 'y' => 810,
            ],
            //提醒长按扫码
            'notice' => [
                'color' => '#333333', 'font_face' => ROOT_PATH.'/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'x' => 30, 'y' => 880,
            ],
            //邀请码文本
            'code_text' => [
                'color' => '#FF2C3C', 'font_face' => ROOT_PATH.'/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'x' => 355, 'y' => 930,
            ],
            //二维码
            'qr' => [
                'w' => 170,'h' => 170, 'x' => 370, 'y' => 730,
            ],
        ];
    }

    //生成用户小程序二维码
    public function makeUserMnpQrcode($code,$content,$img_src)
    {
        try {
            $config = WeChatServer::getMnpConfig();
            $app = Factory::miniProgram($config);
            $response = $app->app_code->get($content.'?invite_code='.$code, [
                'width' => 170,
            ]);

            if ($response instanceof StreamResponse) {
                $response->saveAs('uploads/qr_code/user_share/', $img_src);
                return true;
            }
            if(isset($response['errcode']) && 41030 === $response['errcode']){
                return '商城小程序码，先提交审核并通过';
            }
            return $response['errmsg'];

        } catch (\EasyWeChat\Kernel\Exceptions\Exception $e){
            return $e->getMessage();
        }
    }

    //生成用户分享图
    public function makeUserPoster($user, $content, $url_type, $client)
    {
        try {
            $save_dir = 'uploads/qr_code/user_share/';
            $shareBg = ConfigServer::get('invite', 'poster', 'images/share/share_user_bg.png');
            $background_img = ROOT_PATH .'/'.$shareBg;
            !file_exists($save_dir) && mkdir($save_dir, 0777, true);

            $save_key = 'uid'.$user['id'].$url_type.$client;
            $qr_src = md5($save_key) . '.png';
            $poster_url = ROOT_PATH.'/'.$save_dir . $qr_src;

            $poster_config = $this->userShareConfig();
            //生成二维码
            if($url_type == 'path'){
                $result = $this->makeUserMnpQrcode($user['distribution_code'], $content, $qr_src);
                if(true !== $result){
                    return ['status' => 0, 'msg' => '微信配置错误：'.$result, 'data' => ''];
                }

            }else{
                $qrCode = new QrCode();
                $qrCode->setText($content);
                $qrCode->setSize(1000);
                $qrCode->setWriterByName('png');

                $qrCode->writeFile($poster_url);
            }
            $user_avatar =  UrlServer::getFileUrl($user['avatar'],'share');
            //判断头像是否存在
            if(!check_file_exists($user_avatar)){
                //如果不存在，使用默认头像
                $user_avatar = ROOT_PATH.ConfigServer::get('website', 'user_image');
            }

            $qr_code_logic = new QrCodeLogic();

            //获取背景图
            $share_background_img = imagecreatefromstring(file_get_contents($background_img));
            //合成头像
            $qr_code_logic->writeImg($share_background_img, $user_avatar, $poster_config['head_pic'],true);

            //合成昵称
            $nickname = filterEmoji($user['nickname']);
            $qr_code_logic->writeText($share_background_img, $nickname, $poster_config['nickname']);
            //长按识别
            $notice = '长按识别二维码 >>';
            $qr_code_logic->writeText($share_background_img, $notice, $poster_config['notice']);
            //合成商品标题
            $title = auto_adapt($poster_config['title']['font_size'], 0, $poster_config['title']['font_face'], '邀请你一起来赚大钱', $poster_config['title']['w'],$poster_config['title']['y'],getimagesize($background_img));
            $qr_code_logic->writeText($share_background_img, $title, $poster_config['title']);
            //邀请码
            $qr_code_logic->writeText($share_background_img, '邀请码 '.$user['distribution_code'], $poster_config['code_text']);
            //合成二维码
            $qr_code_logic->writeImg($share_background_img, $poster_url, $poster_config['qr'],false);

            imagepng($share_background_img, $poster_url);

            $file_name = $save_dir . $qr_src;
            $local_uir = ROOT_PATH.'/'.$file_name;
            self::upload($local_uir, $file_name, [ 'delete_file' => 1 ]);

            return ['status' => 1, 'msg' => '', 'data' => $file_name];
        }catch (Exception $e){
            return ['status' => 0, 'msg' => $e->getMessage(), 'data' => ''];
        }
    }
    
    
    /**
     * Notes: 生成的图片根据不同的存储方式储存
     * @param $local_uri mixed 本地图片路径 绝对路径
     * @param $file_name mixed 本地图片路径 相对路径
     * @throws Exception
     * @author 段誉(2021/3/2 18:47)
     */
    public function upload($local_uri, $file_name, $params = [])
    {
        $delete_file = $params['delete_file'] ?? 0;
        $config = [
            'default' => ConfigServer::get('storage', 'default', 'local'),
            'engine' => ConfigServer::get('storage_engine')
        ];

        if (empty($config['engine']['local'])) {
            $config['engine']['local'] = [];
        }

        if ($config['default'] != 'local') {
            $StorageDriver = new StorageDriver($config);
            if (!$StorageDriver->fetch($local_uri, $file_name)) {
                throw new Exception('图片保存出错:' . $StorageDriver->getError());
            }
            //删除本地图片
            if ($delete_file) {
                @unlink($file_name);
            }
        }
    }
    //砍价图片配置
    public function bargainShareConfig(){
        return [
            //会员头像
            'head_pic' => [
                'w' => 64, 'h' => 64, 'x' => 40, 'y' => 20,
            ],
            //会员昵称
            'nickname' => [
                'color' => '#555555', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 19, 'x' => 120, 'y' => 60,
            ],

            'bargain_title' => [
                'color' => '#FF2C3C', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Bold.otf', 'font_size' => 24, 'w' => 360, 'x' => 40, 'y' => 735,
            ],
            'brief_title'   => [
                'color' => '#F95F2F', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 16, 'w' => 360, 'x' => 40, 'y' => 765,
            ],
            //标题
            'title' => [
                'color' => '#333333', 'font_face' => ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 20, 'w' => 360, 'x' => 40, 'y' => 795,
            ],
            //推广主图商品主图
            'main_pic' => [
                'w' => 560, 'h' => 560, 'x' => 40, 'y' => 100,
            ],
            //提醒长按扫码
            'notice' => [
                'color' => '#888888', 'font_face' =>  ROOT_PATH .'/font/SourceHanSansCN-Regular.otf', 'font_size' => 18, 'x' => 432, 'y' => 895,
            ],
            //二维码
            'qr' => [
                'w' => 165,'h' => 165, 'x' => 436, 'y' => 700,
            ],
        ];
    }

    // 生成砍价海报
    public function makeBargainPoster($user,$bargain_launch,$url,$url_type){

        try {
            $save_dir = ROOT_PATH .'/uploads/qr_code/bargain_share/';
            $background_img = ROOT_PATH .'/images/share/share_goods_bg.png';
            !file_exists($save_dir) && mkdir($save_dir,0777,true);

            $cache_key = 'bid' . $bargain_launch['id'].'uid'.$user['id'].$url_type;
            $qr_src = md5($cache_key) . '.png';
            $poster_url = $save_dir . $qr_src;

            $base64 = Cache::get($cache_key);
            if (!empty($base64)) {
                $data = [
                    'code' => 1,
                    'msg' => '海报生成成功',
                    'show' => 0,
                    'data' => $base64
                ];
                return json($data);
            }


            $poster_config = self::bargainShareConfig();
            //生成二维码
            if($url_type == 'path'){
                $scene = 'id='.$bargain_launch['id'];
                $result = $this->makeMnpQrcode($scene,$url,$qr_src,$save_dir);
                if(true !== $result){
                    return JsonServer::error('微信配置错误：'.$result);
                }
            }else{
                $qrCode = new QrCode();
                $qrCode->setText($url);
                $qrCode->setSize(1000);
                $qrCode->setWriterByName('png');
                $qrCode->writeFile($poster_url);

            }
            $user_avatar =  UrlServer::getFileUrl($user['avatar']);
            //判断头像是否存在
            if(!check_file_exists($user_avatar)){
                //如果不存在，使用默认头像
                $user_avatar = ROOT_PATH.ConfigServer::get('website', 'user_image');
            }
            //判断商品主图是否存在
            $goods_image = UrlServer::getFileUrl($bargain_launch['goods_snap']['goods_iamge'],'share');
            if(!check_file_exists($goods_image)){
                //如果不存在，使用默认商品主图
                $goods_image = ROOT_PATH.ConfigServer::get('website', 'goods_image');
            }

            $qr_code_logic = new QrCodeLogic();
            //获取背景图
            $share_background_img = imagecreatefromstring(file_get_contents($background_img));

            //合成头像
            $qr_code_logic->writeImg($share_background_img, $user_avatar, $poster_config['head_pic'],true);

            //合并商品主图
            $qr_code_logic->writeImg($share_background_img, $goods_image, $poster_config['main_pic'],false);

            //合成昵称
            $nickname = filterEmoji($user['nickname']);
            $nickname = '来自'.$nickname.'的分享';
            $qr_code_logic->writeText($share_background_img, $nickname, $poster_config['nickname']);

            //长按识别
            $notice = '长按识别二维码';
            $qr_code_logic->writeText($share_background_img, $notice, $poster_config['notice']);

            //合成砍价标题
            $bargain_title = '我正在参与砍价 还差一步';
            $qr_code_logic->writeText($share_background_img, $bargain_title, $poster_config['bargain_title']);

            //合成简介
            $brief_title = '帮忙砍一刀';
            $qr_code_logic->writeText($share_background_img, $brief_title, $poster_config['brief_title']);

            //合成商品标题
            $goods_name = auto_adapt($poster_config['title']['font_size'], 0, $poster_config['title']['font_face'], $bargain_launch['goods_snap']['name'], $poster_config['title']['w'],$poster_config['title']['y'],getimagesize($background_img));
            $qr_code_logic->writeText($share_background_img, $goods_name, $poster_config['title']);

            //合成二维码
            $qr_code_logic->writeImg($share_background_img, $poster_url, $poster_config['qr'],false);

            imagepng($share_background_img, $poster_url);
            if ($fp = fopen($poster_url, "rb", 0)) {
                $gambar = fread($fp, filesize($poster_url));
                fclose($fp);
                $base64 = chunk_split(base64_encode($gambar));
                $base64 = 'data:image/png;base64,' . $base64;
            }
            //删除文件
            if (strstr($poster_url, $save_dir)) {
                unlink($poster_url);
            }

            $expire = $bargain_launch['launch_end_time'] - time();

            Cache::set($cache_key, $base64, $expire);

            $data = [
                'code' => 1,
                'msg' => '海报生成成功',
                'show' => 0,
                'data' => $base64
            ];
            return json($data);

        }catch (Exception $e){
            return JsonServer::error('海报生成错误:' . $e->getMessage());
        }
    }


    //小程序生成二维码(非永久码)
    public function makeMnpQrcode($scene,$url,$img_src,$save_dir){
        try {
            $config = WeChatServer::getMnpConfig();
            $app = Factory::miniProgram($config);

            $response = $app->app_code->getUnlimit($scene, [
                'page'  => $url,
            ]);
            if ($response instanceof StreamResponse) {
                $response->saveAs($save_dir, $img_src);
                return true;
            }
            if(isset($response['errcode']) && 41030 === $response['errcode']){
                return '商城小程序未上线或商城小程序页面发布';
            }
            return $response['errmsg'];


        } catch (\EasyWeChat\Kernel\Exceptions\Exception $e){

            return $e->getMessage();

        }

    }


    //写入图片
    public function writeImg($poster, $img_uri, $config, $is_rounded = false){
        $pic_img = imagecreatefromstring(file_get_contents($img_uri));
        $is_rounded?$pic_img = rounded_corner($pic_img):'';//切成圆角返回头像资源
        $pic_w = imagesx($pic_img);
        $pic_h = imagesy($pic_img);

        //圆形头像大图合并到海报
        imagecopyresampled($poster, $pic_img,
            $config['x'],
            $config['y'],
            0, 0,
            $config['w'],
            $config['h'],
            $pic_w,
            $pic_h
        );

        return $poster;
    }
    //写入文字
    public function writeText($poster, $text, $config){

        $font_uri = $config['font_face'];
        $font_size = $config['font_size'];
        $color = substr($config['color'],1);
        //颜色转换
        $color= str_split($color, 2);
        $color = array_map('hexdec', $color);
        if (empty($color[3]) || $color[3] > 127) {
            $color[3] = 0;
        }
        //写入文字
        $font_col = imagecolorallocatealpha($poster, $color[0], $color[1], $color[2], $color[3]);
        imagettftext($poster, $font_size,0, $config['x'], $config['y'], $font_col, $font_uri, $text);
        return $poster;

    }
    
    /**
     * @notes 店铺二维码
     * @param $shop_id
     * @param $terminal
     * @return string
     * @author lbzy
     * @datetime 2024-04-01 10:59:40
     */
    function shopQrCode($shop_id, $terminal = null) : string
    {
        try {
            $url        = '/pages/store_index/store_index?id=' . $shop_id;
            $path       = 'uploads/qr_code/shop/';
            $filename   = 'detail.' . md5($shop_id . $terminal) . '.png';
            $local_file = $path . $filename;
            $root_name  = ROOT_PATH . $local_file;
            $save_dir   = ROOT_PATH . '/' . $path;
            $poster_url = $save_dir . $filename;
    
            if (file_exists($root_name)) {
                return UrlServer::getFileUrl($local_file);
            }
            
            ! file_exists($save_dir) && mkdir($save_dir, 0777, true);
    
            switch ($terminal) {
                // 小程序 返回小程序码
                case ClientEnum::mnp:
                    $config = WeChatServer::getMnpConfig();
                    $app    = Factory::miniProgram($config);
            
                    $response = $app->app_code->get($url, [
                        'width' => 240,
                        //...
                    ]);
            
                    if ($response instanceof StreamResponse) {
                        $response->saveAs($save_dir, $filename);
                    }
            
                    break;
                // 其他 返回普通二维码
                default:
                    $qrCode = new QrCode();
                    $qrCode->setText(Request::domain(true) .  '/mobile' . $url);
                    $qrCode->setSize(240);
                    $qrCode->setWriterByName('png');
                    $qrCode->writeFile($poster_url);
                    break;
            }
    
            $this->upload($root_name, $local_file, [ 'delete_file' => 0 ]);
    
            return UrlServer::getFileUrl($local_file);
        } catch(\Throwable $e) {
            Log::write($e->__toString(), 'shop_qr_code_error');
            return '';
        }
    }
}
