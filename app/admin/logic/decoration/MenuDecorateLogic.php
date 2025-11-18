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
use app\common\enum\MenuEnum;
use app\common\server\ConfigServer;
use app\common\server\UrlServer;
use app\common\model\goods\GoodsCategory;
use think\facade\Db;

class MenuDecorateLogic extends Logic {

    /**
     * Notes:获取菜单列表
     * @param $get
     * @return array
     * @author: cjhao 2021/4/28 9:47
     */
    public static function lists($get){

        $where[] = ['del','=',0];
        $where[] = ['decorate_type','=',$get['type']];

        $lists = Db::name('menu_decorate')
                ->where($where)
                ->paginate(['list_rows'=>$get['limit'],'page'=>$get['page']]);

        $list = $lists->items();
        $count = $lists->total();
        $menu_type = 1 == $get['type'] ? 'index' : 'center';
        $categoryNames = GoodsCategory::where('del', 0)->column('name', 'id');
        foreach ($list as $key => $menu){
            if(1 == $menu['link_type']){
                $list[$key]['link_address'] = MenuEnum::getMenu($menu_type,$menu['link_address'])['name'] ?? '';
            } elseif (4 == $menu['link_type']) {
                $list[$key]['link_address'] = $categoryNames[$menu['category_id']] ?? '';
            }
            $list[$key]['image'] = empty($list[$key]['image']) ? '' : UrlServer::getFileUrl($list[$key]['image']);
        }


        return ['count'=>$count,'lists'=>$list];
    }

    /**
     * Notes:添加菜单
     * @param $post
     * @author: cjhao 2021/5/18 17:07
     */
    public static function add($post){
        $linkAddress = self::buildLinkAddress($post);
        $data = [
            'name'              => $post['name'],
            'decorate_type'     => $post['decorate_type'],
            'image'             => clearDomain($post['image']),
            'link_type'         => $post['link_type'],
            'link_address'      => $linkAddress,
            'category_id'       => 4 == $post['link_type'] ? intval($post['category_id'] ?? 0) : 0,
            'appid'             => 3 == $post['link_type'] ? trim($post['appid'] ?? '') : '',
            'sort'              => $post['sort'],
            'is_show'           => $post['is_show'],
            'create_time'       => time(),
        ];
        return Db::name('menu_decorate')->insert($data);
    }

    /**
     * Notes:编辑菜单
     * @param $post
     * @return int
     * @author: cjhao 2021/5/18 17:38
     */
    public static function edit($post){
        $linkAddress = self::buildLinkAddress($post);
        $data = [
            'name'              => $post['name'],
            'image'             => clearDomain($post['image']),
            'link_type'         => $post['link_type'],
            'link_address'      => $linkAddress,
            'category_id'       => 4 == $post['link_type'] ? intval($post['category_id'] ?? 0) : 0,
            'appid'             => 3 == $post['link_type'] ? trim($post['appid'] ?? '') : '',
            'sort'              => $post['sort'],
            'is_show'           => $post['is_show'],
            'update_time'       => time(),
        ];

        return Db::name('menu_decorate')->where(['id'=>$post['id']])->update($data);
    }

    /**
     * 处理菜单跳转地址
     * @param array $post
     * @return string
     */
    protected static function buildLinkAddress($post)
    {
        $linkAddress = '';
        switch ($post['link_type']) {
            case 1:
                $linkAddress = $post['menu'] ?? '';
                break;
            case 3:
                $linkAddress = $post['mini_url'] ?? '';
                break;
            case 4:
                $linkAddress = $post['category_url'] ?? '';
                break;
            default:
                $linkAddress = $post['url'] ?? '';
                break;
        }

        return trim((string)$linkAddress);
    }


    /**
     * Notes:删除菜单
     * @param $id
     * @return int
     * @author: cjhao 2021/5/18 18:38
     */
    public static function del($id){
        return Db::name('menu_decorate')
            ->where(['id'=>$id,'del'=>0])
            ->update(['update_time'=>time(),'del'=>1]);
    }


    /**
     * Notes:切换菜单状态
     * @param $post
     * @return int
     * @author: cjhao 2021/5/18 18:38
     */
    public static function swtichStatus($post){
        return Db::name('menu_decorate')
            ->update($post);
    }

    /**
     * Notes:获取菜单
     * @param $id
     * @return array|\think\Model|null
     * @author: cjhao 2021/4/28 11:47
     */
    public static function getMenuDecorate($id){
        $info =  Db::name('menu_decorate')
                ->where(['del'=>0,'id'=>$id])
                ->find();
        $info['image'] = empty($info['image']) ? '' : UrlServer::getFileUrl($info['image']);
        $info['category_id'] = $info['category_id'] ?? 0;

        return $info;

    }


    /**
     * Notes:其他设置
     * @param $post
     * @return bool
     * @author: cjhao 2021/5/19 14:20
     */
    public static function otherSet($post){
        //首页
        if(1 == $post['type']){

            ConfigServer::set('decoration_index','host_show',$post['host_show']);
            ConfigServer::set('decoration_index','new_show',$post['new_show']);
            ConfigServer::set('decoration_index','shop_show',$post['shop_show']);
            ConfigServer::set('decoration_index','community_show',$post['community_show']);
            ConfigServer::set('decoration_index','live_room',$post['live_room']);
            ConfigServer::set('decoration_index','background_image',UrlServer::setFileUrl($post['background_image'] ?? ''));

        }else{
            ConfigServer::set('decoration_center','background_image',UrlServer::setFileUrl($post['background_image'] ?? ''));
        }
        return true;

    }


    /**
     * Notes:获取其他设置
     * @param $type
     * @return array
     * @author: cjhao 2021/5/19 14:37
     */
    public static function getOtherSet($type) {
        if(1 == $type) {
            $data = [
                'host_show' => ConfigServer::get('decoration_index','host_show',1),
                'new_show' => ConfigServer::get('decoration_index','new_show',1),
                'shop_show' => ConfigServer::get('decoration_index','shop_show',1),
                'community_show' => ConfigServer::get('decoration_index','community_show',1),
                'live_room' => ConfigServer::get('decoration_index','live_room',1),
                'background_image' => ConfigServer::get('decoration_index','background_image',''),
            ];
            $data['background_image'] && $data['background_image'] = UrlServer::getFileUrl($data['background_image']);
        }else{
           $background_image =  ConfigServer::get('decoration_center','background_image','');
           $background_image && $background_image = UrlServer::getFileUrl($background_image);
           $data['background_image'] = $background_image;
        }

        return $data;
    }

    /**
     * 底部导航 - 列表
     */
    public static function bottomNavigation($get)
    {
        $lists = Db::name('dev_navigation')
            ->where('del', 0)
            ->page($get['page'], $get['limit'])
            ->order('id', 'desc')
            ->select()
            ->toArray();
        $count = Db::name('dev_navigation')->count();
        foreach($lists as &$item) {
            $item['selected_icon'] = UrlServer::getFileUrl($item['selected_icon']);
            $item['un_selected_icon'] = UrlServer::getFileUrl($item['un_selected_icon']);
            $item['status_text'] = $item['status'] ? '显示' : '隐藏';
        }

        $data = [
            'count' => $count,
            'lists' => $lists
        ];
        return $data;
    }

    /**
     * 底部导航 - 详情
     */
    public static function getNavigation($id)
    {
        $navigation = Db::name('dev_navigation')->where('id', $id)->find();
        $navigation['selected_icon'] = $navigation['selected_icon'] ? UrlServer::getFileUrl($navigation['selected_icon']) : '';
        $navigation['un_selected_icon'] = $navigation['un_selected_icon'] ? UrlServer::getFileUrl($navigation['un_selected_icon']) : '';
        return $navigation;
    }

    /**
     * 底部导航 - 编辑
     */
    public static function editNavigation($post)
    {
        try {
            if(empty($post['name'])) {
                throw new \Exception( '导航名称不能为空');
            }

            $count = Db::name('dev_navigation')->where([
                ['del', '=', 0],
                ['name', '=', trim($post['name']) ],
                ['id', '<>', $post['id']]
            ])->count();

            if($count) {
                throw new \Exception( '导航名称已存在');
            }

            // 首页菜单不可隐藏，最少保留两个导航
            $checkRes = self::checkAbleHideNavBtn($post['id'], $post['status']);
            if (true !== $checkRes) {
                throw new \Exception($checkRes);
            }

            $updateData = [
                'name' => trim($post['name']),
                'status' => $post['status'] ?? 1,
                'update_time' => time()
            ];
            if(isset($post['selected_icon'])) {
                $updateData['selected_icon'] = trim(UrlServer::setFileUrl($post['selected_icon']));
            }
            if(isset($post['un_selected_icon'])) {
                $updateData['un_selected_icon'] = trim(UrlServer::setFileUrl($post['un_selected_icon']));
            }
            Db::name('dev_navigation')->where('id', $post['id'])->update($updateData);

            return true;
        } catch (\Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * @notes 校验能都隐藏导航菜单
     * @param $navId
     * @param $status
     * @return bool|string
     * @author 段誉
     * @date 2023/2/21 15:13
     */
    public static function checkAbleHideNavBtn($navId, $status)
    {
        if ($status) {
            return true;
        }

        $first = Db::name('dev_navigation')
            ->where('del', 0)
            ->order('id', 'desc')
            ->findOrEmpty();

        if ($first['id'] == $navId) {
            return '首页导航不可隐藏';
        }

        $hideCount = Db::name('dev_navigation')
            ->where(['del' => 0, 'status' => 1])
            ->count();
        
        if ($hideCount <= 2) {
            return '最少保留两个导航菜单';
        }

        return true;
    }

}
