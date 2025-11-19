<?php

use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use think\facade\Db;
use app\common\model\user\User;
use app\common\model\goods\Goods;
use app\common\model\Agent;

/**
 * Notes: 生成随机长度字符串
 * @param $length
 * @author FZR(2021/1/28 10:36)
 * @return string|null
 */
function getRandChar($length)
{
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;

    for ($i = 0;
         $i < $length;
         $i++) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}

/**
 * Notes: 生成密码
 * @param $plaintext
 * @param $salt
 * @author FZR(2021/1/28 15:30)
 * @return string
 */
function generatePassword($plaintext, $salt)
{
    $salt = md5('y' . $salt . 'x');
    $salt .= '2021';
    return md5($plaintext . $salt);
}


/**
 * Notes: 大写字母
 * @author 段誉(2021/4/15 15:55)
 * @return array
 */
function getCapital()
{
    return  range('A','Z');
}

/**
 * 线性结构转换成树形结构
 * @param array $data 线性结构数组
 * @param string $sub_key_name 自动生成子数组名
 * @param string $id_name 数组id名
 * @param string $parent_id_name 数组祖先id名
 * @param int $parent_id 此值请勿给参数
 * @return array
 */
function linear_to_tree($data, $sub_key_name = 'sub', $id_name = 'id', $parent_id_name = 'pid', $parent_id = 0)
{
  $tree = [];
  foreach ($data as $row) {
    if ($row[$parent_id_name] == $parent_id) {
      $temp = $row;
      $temp[$sub_key_name] = linear_to_tree($data, $sub_key_name, $id_name, $parent_id_name, $row[$id_name]);
      $tree[] = $temp;
    }
  }
  return $tree;
}

/**
 * User: 意象信息科技 lr
 * Desc: 下载文件
 * @param $url 文件url
 * @param $save_dir 保存目录
 * @param $file_name 文件名
 * @return string
 */
function download_file($url, $save_dir, $file_name)
{
    if (!file_exists($save_dir)) {
        mkdir($save_dir, 0775, true);
    }
    $file_src = $save_dir . $file_name;
    file_exists($file_src) && unlink($file_src);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $file = curl_exec($ch);
    curl_close($ch);
    $resource = fopen($file_src, 'a');
    fwrite($resource, $file);
    fclose($resource);
    if (filesize($file_src) == 0) {
        unlink($file_src);
        return '';
    }
    return $file_src;
}

/**
 * 生成会员码
 * @return 会员码
 */
function create_user_sn($prefix = '', $length = 8)
{
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= mt_rand(0, 9);
    }
    $sn = $prefix . $rand_str;
    $user = User::where(['sn' => $sn])->findOrEmpty();
    if (!$user->isEmpty()) {
        return create_user_sn($prefix, $length);
    }
    return $sn;
}

//生成用户邀请码
function generate_invite_code()
{
    $letter_all = range('A', 'Z');
    shuffle($letter_all);
    //排除I、O字母
    $letter_array = array_diff($letter_all, ['I', 'O', 'D']);
    //排除1、0
    $num_array = range('2', '9');
    shuffle($num_array);

    $pattern = array_merge($num_array, $letter_array, $num_array);
    shuffle($pattern);
    $pattern = array_values($pattern);

    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $pattern[mt_rand(0, count($pattern) - 1)];
    }

    $code = strtoupper($code);
    $check = User::where('distribution_code', $code)->findOrEmpty();
    if (!$check->isEmpty()) {
        return generate_invite_code();
    }
    return $code;
}

/**
 * 生成代理邀请码（全局唯一）
 * @return string
 */
function generate_agent_invite_code()
{
    $letter_all = range('A', 'Z');
    shuffle($letter_all);
    //排除I、O字母
    $letter_array = array_diff($letter_all, ['I', 'O', 'D']);
    //排除1、0
    $num_array = range('2', '9');
    shuffle($num_array);

    $pattern = array_merge($num_array, $letter_array, $num_array);
    shuffle($pattern);
    $pattern = array_values($pattern);

    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $pattern[mt_rand(0, count($pattern) - 1)];
    }

    $code = strtoupper($code);
    // 检查agent表中的invite_code是否已存在
    $check = Agent::where('invite_code', $code)->where('del', 0)->findOrEmpty();
    if (!$check->isEmpty()) {
        return generate_agent_invite_code();
    }
    return $code;
}

/**
 * User: 意象信息科技 lr
 * Desc: 数组成功拼装
 * @param string $msg
 * @param array $data
 * @param int $code
 * @param int $show
 * @return array
 */
function data_success($msg = '', $data = [], $code = 1, $show = 1)
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
        'show' => $show,
    ];
    return $result;
}

/**
 * User: 意象信息科技 lr
 * Desc: 组装失败数据
 * @param string $msg
 * @param array $data
 * @param int $code
 * @param int $show
 * @return array
 */
function data_error($msg = '', $data = [], $code = 0, $show = 1)
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
        'show' => $show,
    ];
    return $result;
}

/**
 * User: 意象信息科技 cjh
 * Desc: 返回是否有下一页
 * @param $count (总记录数)
 * @param $page (当前页码)
 * @param $size (每页记录数)
 * @return int
 */
function is_more($count, $page, $size)
{
    $more = 0;

    $last_page = ceil($count / $size);      //总页数、也是最后一页

    if ($last_page && $last_page > $page) {
        $more = 1;
    }
    return $more;
}

/**
 * User: 意象信息科技 lr
 * Desc：生成密码密文
 * @param $plaintext string 明文
 * @param $salt string 密码盐
 * @return string
 */
function create_password($plaintext, $salt)
{
    $salt = md5('y' . $salt . 'x');
    $salt .= '2021';
    return md5($plaintext . $salt);
}

/**
 * User: 意象信息科技 mjf
 * Desc: 用时间生成订单编号
 * @param $table
 * @param $field
 * @param string $prefix
 * @param int $rand_suffix_length
 * @param array $pool
 * @return string
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 */
function createSn($table, $field, $prefix = '', $rand_suffix_length = 4, $pool = [])
{
    $suffix = '';
    for ($i = 0; $i < $rand_suffix_length; $i++) {
        if (empty($pool)) {
            $suffix .= rand(0, 9);
        } else {
            $suffix .= $pool[array_rand($pool)];
        }
    }
    $sn = $prefix . date('YmdHis') . $suffix;
    if (Db::name($table)->where($field, $sn)->find()) {
        return createSn($table, $field, $prefix, $rand_suffix_length, $pool);
    }
    return $sn;
}

/**
 * User: 意象信息科技 lr
 * Desc: 表单多维数据转换
 * 例：
 * 转换前：{"x":0,"a":[1,2,3],"b":[11,22,33],"c":[111,222,3333,444],"d":[1111,2222,3333]}
 * 转换为：[{"a":1,"b":11,"c":111,"d":1111},{"a":2,"b":22,"c":222,"d":2222},{"a":3,"b":33,"c":3333,"d":3333}]
 * @param $arr array 表单二维数组
 * @param $fill boolean fill为false，返回数据长度取最短，反之取最长，空值自动补充
 * @return array
 */
function form_to_linear($arr, $fill = false)
{
    $keys = [];
    $count = $fill ? 0 : PHP_INT_MAX;
    foreach ($arr as $k => $v) {
        if (is_array($v)) {
            $keys[] = $k;
            $count = $fill ? max($count, count($v)) : min($count, count($v));
        }
    }
    if (empty($keys)) {
        return [];
    }
    $data = [];
    for ($i = 0; $i < $count; $i++) {
        foreach ($keys as $v) {
            $data[$i][$v] = isset($arr[$v][$i]) ? $arr[$v][$i] : null;
        }
    }
    return $data;
}

/**
 * note 生成验证码
 * @param int $length 验证码长度
 * @return string
 */
function create_sms_code($length = 4)
{
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= rand(0, 9);
    }
    return $code;
}

/**
 * 生成商品编码
 * 8位
 */
function create_goods_code($shop_id)
{
    $code =  mt_rand(10000000, 99999999);
    $goods = Goods::where([
        'code' => $code,
        'shop_id' => $shop_id,
        'del' => 0
    ])->findOrEmpty();
    if($goods->isEmpty()) {
        return $code;
    }
    create_goods_code($shop_id);
}

/**
 * 图片去除域名
 */
function clearDomain($x)
{
    if(is_array($x)) {
        $newX = [];
        foreach($x as $v) {
            $newX[] = trim(UrlServer::setFileUrl($v));
        }
        return $newX;
    }
    return  trim(UrlServer::setFileUrl($x));
}

/*
 * 生成优惠券码 排除1、0、I、O相似的数字和字母
 */
function create_coupon_code()
{
    $letter_all = range('A', 'Z');
    shuffle($letter_all);
    //排除I、O字母
    $letter_array = array_diff($letter_all, ['I', 'O']);
    //随机获取四位字母
    $letter = array_rand(array_flip($letter_array), 4);
    //排除1、0
    $num_array = range('2', '9');
    shuffle($num_array);
    //获取随机六位数字
    $num = array_rand(array_flip($num_array), 6);
    $code = implode('', array_merge($letter, $num));
    do {
        $exist_code =\app\common\model\coupon\CouponList::where(['del' => 0, 'coupon_code' => $code])->find();
    } while ($exist_code);
    return $code;
}

/**
 * 浮点数去除无效的0
 */
function clearZero($float)
{
    if($float == intval($float)) {
        return intval($float);
    }else if($float == sprintf('%.1f', $float)) {
        return sprintf('%.1f', $float);
    }
    return $float;
}

/**
 * 是否在cli模式
 */
if (!function_exists('is_cli')) {
    function is_cli()
    {
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }
}

function real_path()
{
    if (substr(strtolower(PHP_OS), 0, 3) == 'win') {
        $ini = ini_get_all();
        $path = $ini['extension_dir']['local_value'];
        $php_path = str_replace('\\', '/', $path);
        $php_path = str_replace(array('/ext/', '/ext'), array('/', '/'), $php_path);
        $real_path = $php_path . 'php.exe';
    } else {
        $real_path = PHP_BINDIR . '/php';
    }
    if (strpos($real_path, 'ephp.exe') !== FALSE) {
        $real_path = str_replace('ephp.exe', 'php.exe', $real_path);
    }
    return $real_path;
}

/**
 * 是否为移动端
 */
function is_mobile()
{
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'textml') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'textml')))) {
            return true;
        }
    }
    return false;
}

/**
 * Notes:判断文件是否存在（远程和本地文件）
 * @param $file string 完整的文件链接
 * @return bool
 */
function check_file_exists($file)
{
    //远程文件
    if ('http' == strtolower(substr($file, 0, 4))) {

        $header = get_headers($file, true);

        return isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'));

    } else {

        return file_exists($file);
    }
}

/**
 * 将图片切成圆角
 */
function rounded_corner($src_img)
{
    $w = imagesx($src_img);//微信头像宽度 正方形的
    $h = imagesy($src_img);//微信头像宽度 正方形的
    $w = min($w, $h);
    $h = $w;
    $img = imagecreatetruecolor($w, $h);
    //这一句一定要有
    imagesavealpha($img, true);
    //拾取一个完全透明的颜色,最后一个参数127为全透明
    $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
    imagefill($img, 0, 0, $bg);
    $r = $w / 2; //圆半径
//    $y_x = $r; //圆心X坐标
//    $y_y = $r; //圆心Y坐标
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $rgbColor = imagecolorat($src_img, $x, $y);
            if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                imagesetpixel($img, $x, $y, $rgbColor);
            }
        }
    }
    unset($src_img);
    return $img;
}

/**
 * Notes:去掉名称中的表情
 * @param $str
 * @return string|string[]|null
 * @author: cjhao 2021/3/29 15:56
 */
function filterEmoji($str)
{
    $str = preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);
    return $str;
}

/***
 * 生成海报自动适应标题
 * @param $size
 * @param int $angle
 * @param $fontfile
 * @param $string
 * @param $width
 * @param $height
 * @param $bg_height
 * @return string
 */
function auto_adapt($size, $angle = 0, $fontfile, $string, $width, $height, $bg_height)
{
    $content = "";
    // 将字符串拆分成一个个单字 保存到数组 letter 中
    for ($i = 0; $i < mb_strlen($string); $i++) {
        $letters[] = mb_substr($string, $i, 1);
    }

    foreach ($letters as $letter) {
        $str = $content . " " . $letter;
        $box = imagettfbbox($size, $angle, $fontfile, $str);

        $total_height = $box[1] + $height;
        if ($bg_height[1] - $total_height < $size) {
            break;
        }
        //右下角X位置,判断拼接后的字符串是否超过预设的宽度
        if (($box[2] > $width) && ($content !== "")) {
            if ($bg_height[1] - $total_height < $size * 2) {
                break;
            }
            $content .= "\n";
        }
        $content .= $letter;
    }
    return $content;
}

/**
 * Notes:生成一个范围内的随机浮点数
 * @param int $min 最小值
 * @param int $max 最大值
 * @return float|int 返回随机数
 */
function random_float($min = 0, $max = 1)
{
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}


/**
 * Notes: 获取文件扩展名
 * @param $file
 * @author 段誉(2021/7/7 18:03)
 * @return mixed
 */
if (!function_exists('get_extension')) {
    function get_extension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
}


/**
 * Notes: 遍历指定目录下的文件(目标目录,排除文件)
 * @param $dir 目标文件
 * @param string $exclude_file 要排除的文件
 * @param string $target_suffix 指定后缀
 * @author 段誉(2021/7/7 18:04)
 * @return array|bool
 */

if (!function_exists('get_scandir')) {
    function get_scandir($dir, $exclude_file = '', $target_suffix = '')
    {
        if (!file_exists($dir)) {
            return [];
        }

        if (empty(trim($dir))) {
            return false;
        }

        $files = scandir($dir);
        $res = [];
        foreach ($files as $item) {
            if ($item == "." || $item == ".." || $item == $exclude_file) {
                continue;
            }
            if (!empty($target_suffix)) {
                if (get_extension($item) == $target_suffix) {
                    $res[] = $item;
                }
            } else {
                $res[] = $item;
            }
        }

        if (empty($item)) {
            return false;
        }
        return $res;
    }
}



/**
 * Notes: 解压压缩包
 * @param $file 压缩包路径
 * @param $save_dir 保存路径
 * @author 段誉(2021/7/7 18:11)
 * @return bool
 */
if (!function_exists('unzip')) {
    function unzip($file, $save_dir)
    {
        if (!file_exists($file)) {
            return false;
        }
        $zip = new \ZipArchive();
        if ($zip->open($file) !== TRUE) {//中文文件名要使用ANSI编码的文件格式
            return false;
        }
        $zip->extractTo($save_dir);
        $zip->close();
        return true;
    }
}


/**
 * Notes: 删除目标目录
 * @param $path
 * @param $delDir
 * @author 段誉(2021/7/7 18:19)
 * @return bool
 */
if (!function_exists('del_target_dir')) {
    function del_target_dir($path, $delDir)
    {
        //没找到，不处理
        if (!file_exists($path)) {
            return false;
        }

        $handle = opendir($path);
        if ($handle) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$path/$item")) {
                        del_target_dir("$path/$item", $delDir);
                    } else {
                        unlink("$path/$item");
                    }
                }
            }
            closedir($handle);
            if ($delDir) {
                return rmdir($path);
            }
        } else {
            if (file_exists($path)) {
                return unlink($path);
            }
            return false;
        }
    }
}


/**
 * Notes: 获取本地版本数据
 * @return mixed
 * @author 段誉(2021/7/7 18:18)
 */
if (!function_exists('local_version')) {
    function local_version()
    {
        if(!file_exists('./upgrade/')) {
            // 若文件夹不存在，先创建文件夹
            mkdir('./upgrade/', 0777, true);
        }
        if(!file_exists('./upgrade/version.json')) {
            // 获取本地版本号
            $version = config('project.version');
            $data = ['version' => $version];
            $src = './upgrade/version.json';
            // 新建文件
            file_put_contents($src, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        $json_string = file_get_contents('./upgrade/version.json');
        // 用参数true把JSON字符串强制转成PHP数组
        $data = json_decode($json_string, true);
        return $data;
    }
}


/**
 * Notes: 获取ip
 * @author 段誉(2021/7/9 10:19)
 * @return array|false|mixed|string
 */
if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        if (!isset($_SERVER)) {
            return getenv('SERVER_ADDR');
        }

        if($_SERVER['SERVER_ADDR']) {
            return $_SERVER['SERVER_ADDR'];
        }

        return $_SERVER['LOCAL_ADDR'];
    }
}


/**
 * @notes 友好时间提示
 * @param $time
 * @return false|string
 * @author 段誉
 * @date 2022/5/6 9:45
 */
function friend_date($time)
{
    if (empty($time)) {
        return false;
    }

    $d = time() - intval($time);

    $ld = $time - mktime(0, 0, 0, 0, 0, date('Y'));  //年
    $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y'));  //月
    $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y'));  //前天
    $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));  //昨天
    $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y'));  //今天
    $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));  //明天

    $timeDay = date('d', $time);
    $nowDay =  date('d', time());

    if (0 == $d) {
        return '刚刚';
    }

    switch ($d) {
        case $d < $td :
            $fdate = '后天' . date('H:i', $time);
            break;
        case  $d < 0 && $timeDay == $nowDay:
            $fdate = '今天' . date('H:i', $time);
            break;
        case  $d < 0:
            $fdate = '明天' . date('H:i', $time);
            break;
        case  $d < 60:
            $fdate = $d . '秒前';
            break;
        case  $d < 3600:
            $fdate = floor($d / 60) . '分钟前';
            break;
        case  $d < $dd :
            $fdate = floor($d / 3600) . '小时前';
            break;
        case  $d < $yd :
            $fdate = '昨天' . date('H:i', $time);
            break;
        case  $d < $byd :
            $fdate = '前天' . date('H:i', $time);
            break;
        case  $d < $md :
            $fdate = date('m月d日 H:i', $time);
            break;
        case  $d < $ld :
            $fdate = date('m月d日', $time);
            break;
        default :
            $fdate = date('Y年m月d日', $time);
            break;
    }
    return $fdate;
}


/**
 * @notes 生成随机数字
 * @param $table
 * @param string $field
 * @param int $length
 * @param string $prefix
 * @return string
 * @author 段誉
 * @date 2022/11/1 15:51
 */
function create_rand_number($table, $field = 'sn', $length = 8, $prefix = '')
{
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= mt_rand(0, 9);
    }
    $sn = $prefix . $rand_str;
    if (Db::name($table)->where($field, $sn)->find()) {
        return create_rand_number($table, $field, $length, $prefix);
    }
    return $sn;
}


/**
 * @notes 文本超出隐藏
 * @param $string
 * @param $length
 * @return mixed|string
 * @author 段誉
 * @date 2022/10/31 14:14
 */
function text_out_hidden($string, $length)
{
    if (mb_strlen($string) > $length) {
        $string = mb_substr($string, 0, $length) . '..';
    }
    return $string;
}



/**
 * @notes Html中的图片绝对路径转为相对路径
 * @param $content
 * @return array|string|string[]
 * @author ljj
 * @date 2022/3/28 3:27 下午
 */
function HtmlSetImage($content)
{
    $domain = UrlServer::getFileUrl('/');

    preg_match_all("/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i",$content,$matches);
    if(!empty($matches)){
        $imgurl = $matches[2];
        foreach($imgurl as $val){
            $setVal = str_replace($domain, '', $val);
            $content = str_replace($val,$setVal,$content);
        }
    }

    return $content;
}


/**
 * @notes Html中的图片相对路径转为绝对路径
 * @param $content
 * @author ljj
 * @date 2022/3/28 3:35 下午
 */
function HtmlGetImage($content)
{
    $domain = UrlServer::getFileUrl('/');

    preg_match_all("/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i",$content,$matches);
    if(!empty($matches)){
        $imgurl = $matches[2];
        foreach($imgurl as $val){
            $content = str_replace($val,$domain.$val,$content);
        }
    }

    return $content;
}