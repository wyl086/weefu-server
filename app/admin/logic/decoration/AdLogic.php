<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------
namespace app\admin\logic\decoration;
use app\common\basics\Logic;
use app\common\enum\AdEnum;
use app\common\server\UrlServer;
use think\facade\Db;

class AdLogic extends Logic{
    /**
     * Notes:广告列表
     * @param $get
     * @return array
     * @author: cjhao 2021/4/20 10:23
     */
    public static function lists($get){
        $where[] = ['A.del','=',0];
        $where[] = ['A.terminal','=',$get['terminal']];

        if(isset($get['pid']) && $get['pid']){
            $where[] = ['A.pid','=',$get['pid']];
        }

        $lists = Db::name('ad')->alias('A')
                ->join('ad_position AP','A.pid = AP.id')
                ->where($where)
                ->order('A.sort asc, A.id desc')
                ->field('A.*,AP.name as pname')
                ->paginate(['list_rows'=>$get['limit'],'page'=>$get['page']]);

        $list = $lists->items();
        $count = $lists->total();

        foreach ($list as $key => $ad){
            $list[$key]['terminal_desc'] = AdEnum::getTerminal($ad['terminal']);
            $list[$key]['image'] = UrlServer::getFileUrl($ad['image']);
            switch ($ad['link_type']) {
                case 1:
                    $page = AdEnum::getLinkPage($ad['terminal'], $ad['link']);
                    $url = '商城页面：' . $page['name'];
                    break;
                case 2:
                    $goods = Db::name('goods')
                        ->where(['id' => $ad['link']])
                        ->field('name,min_price,max_price')
                        ->find();
                    if ($goods) {
                        $price = '￥' . $goods['max_price'];
                        if ($goods['max_price'] !== $goods['min_price']) {
                            $price = '￥' . $goods['min_price'] . '~' . $goods['max_price'];
                        }
                        $url = '商品页面:' . $goods['name'] . '价格：' . $price;
                    }
                    break;
                case 3:
                    $url = '自定义链接：' . $ad['link'];
                    break;
                default:
                    $url = '';
                    break;
            }
            $list[$key]['link'] = $url;
        }


        return ['count'=>$count,'lists'=>$list];
    }

    /**
     * Notes:添加广告
     * @param $post
     * @return bool
     * @author: cjhao 2021/4/20 10:54
     */
    public static function add($post){

        $post['status'] = isset($post['status']) ? $post['status'] : 0;
        $post['link_type'] = isset($post['link_type']) ? $post['link_type'] : '';
        $link = '';

        switch ($post['link_type']) {
            case '1':
                $link = $post['page'];
                break;
            case '2':
                $link = $post['goods_id'];
                break;
            case '3':
                $link = $post['url'];
                break;
        }
        $now = time();
        $data = [
            'title'         => $post['title'],
            'terminal'      => $post['terminal'],
            'pid'           => $post['pid'],
            'image'         => isset($post['image']) ? clearDomain($post['image']) : '',
            'link_type'     => $post['link_type'],
            'link'          => $link,
            'status'        => $post['status'],
            'category_id'   => $post['category_id'] ?? 0,
            'sort'          =>  $post['sort'] > 0 ? $post['sort'] : 50,
            'create_time'   => $now,
        ];

        return Db::name('ad')->insert($data);
    }

    /**
     * Notes:编辑广告
     * @param $post
     * @return bool
     * @author: cjhao 2021/4/20 10:54
     */
    public static function edit($post){

        $post['status'] = isset($post['status']) ? $post['status'] : 0;
        $post['link_type'] = isset($post['link_type']) ? $post['link_type'] : '';
        $link = '';

        switch ($post['link_type']) {
            case '1':
                $link = $post['page'];
                break;
            case '2':
                $link = $post['goods_id'];
                break;
            case '3':
                $link = $post['url'];
                break;
        }
        $now = time();
        $data = [
            'title'         => $post['title'],
            'pid'           => $post['pid'],
            'image'         => isset($post['image']) ? clearDomain($post['image']) : '',
            'link_type'     => $post['link_type'],
            'link'          => $link,
            'status'        => $post['status'],
            'category_id'   => $post['category_id'] ?? 0,
            'sort'          => $post['sort'] > 0 ? $post['sort'] : 50,
            'update_time'   => $now,
        ];

        return Db::name('ad')->where(['id'=>$post['id']])->update($data);;
    }

    /**
     * Notes:获取广告信息
     * @param $id
     * @return array|\think\Model|null
     * @author: cjhao 2021/4/20 10:55
     */
    public static function getAd($id){
        $detail =  Db::name('ad')
                ->where(['id'=>$id,'del'=>0])
                ->find();
        if($detail) {
            $detail['image'] = UrlServer::getFileUrl($detail['image']);
        }

        $detail['goods'] = [];
        if ($detail['link_type'] == 2) {
            $goods = Db::name('goods')
                ->where(['id' => $detail['link']])
                ->field('id,name,image,min_price,max_price')
                ->find();
            $price = '￥' . $goods['max_price'];
            if ($goods['max_price'] !== $goods['min_price']) {
                $price = '￥' . $goods['min_price'] . '~' . $goods['max_price'];
            }
            $goods['price'] = $price;
            $detail['goods'] = $goods;
        }
        return $detail;
    }

    /**
     * Notes:删除广告
     * @param $id
     * @return int
     * @author: cjhao 2021/4/20 10:55
     */
    public static function del($id){
        return Db::name('ad')
                ->where(['id'=>$id,'del'=>0])
                ->update(['update_time'=>time(),'del'=>1]);
    }


    /**
     * Notes:切换广告状态
     * @param $post
     * @return int
     * @author: cjhao 2021/4/20 10:56
     */
    public static function swtichStatus($post){
        return Db::name('ad')
            ->update($post);
    }


    /**
     * Notes:获取广告位列表
     * @param $terminal
     * @return array|\think\Model|null
     * @author: cjhao 2021/4/20 11:04
     */
    public static function getPositionList($terminal){
        return Db::name('ad_position')
                ->where(['del'=>0,'terminal'=>$terminal,'status'=>1])
                ->field('id,name,height,width')
                ->select();
    }

    /**
     * Notes:获取分类列表
     * @return \think\Collection
     * @author: cjhao 2021/4/20 11:06
     */
    public static function getCategoryList(){
        return Db::name('goods_category')
                ->where(['del'=>0,'level'=>1])
                ->select();

    }



}
