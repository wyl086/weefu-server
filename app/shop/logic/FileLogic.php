<?php
// +----------------------------------------------------------------------
// | multshop多商户商城系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习系统程序代码，建议反馈是我们前进的动力
// | gitee下载：https://gitee.com/multshop_gitee
// | github下载：https://github.com/multshop-github
// | 访问官网：https://www.multshop.cn
// | 访问社区：https://home.multshop.cn
// | 访问手册：http://doc.multshop.cn
// | 微信公众号：multshop技术社区
// | multshop系列产品在gitee、github等公开渠道开源版本可免费商用，未经许可不能去除前后端官方版权标识
// |  multshop系列产品收费版本务必购买商业授权，购买去版权授权后，方可去除前后端官方版权标识
// | 禁止对系统程序代码以任何目的，任何形式的再发布
// | multshop团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | author: multshop.cn.team
// +----------------------------------------------------------------------

namespace app\shop\logic;


use app\common\basics\Logic;
use app\common\enum\FileEnum;
use app\common\model\File;
use app\common\model\FileCate;
use Exception;

class FileLogic extends Logic
{

    /**
     * NOTE: 获取文件列表
     * @author: 张无忌
     * @param $get
     * @return array|\think\Paginator
     */
    public static function getFile($get)
    {
        try {
            $where = [
                ['del', '=', 0],
                ['type', '=', $get['type'] ?? FileEnum::IMAGE_TYPE],
                ['shop_id', '=', $get['shop_id']]
            ];

            if (!empty($get['cid']) and $get['cid'] > 0) {
                $lists = FileCate::where(['del'=>0 ])->select();
                $lists = !empty($lists) ? $lists->toArray() : [];
                $childs = self::getChildCid($lists, $get['cid'], true);
                array_push($childs, $get['cid']);
                $where[] = ['cid', 'in', $childs];
            }

            $model = new File();
            return $model->field(true)
                ->where($where)
                ->order('id', 'desc')
                ->paginate([
                    'page'      => $get['page'] ?? 1,
                    'list_rows' => $get['limit'] ?? 20,
                    'var_page' => 'page'
                ])->toArray();

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * NOTE: 获取后代分类
     * @author: 张无忌
     * @param $cid
     * @param array $ids
     */
    public static function getChildCid($lists, $cid, $clear)
    {
        static $temp = [];
        if($clear) {
            $temp = [];
        }
        foreach($lists as $item) {
            if($item['pid'] == $cid) {
                $temp[] = $item['id'];
                self::getChildCid($lists, $item['id'], false);
            }
        }
        return $temp;
    }

    /**
     * NOTE: 移动文件
     * @param $post
     * @return bool
     * @author: 张无忌
     */
    public static function move($post)
    {
        try {
            $model = new File();
//            $file = $model->field(true)->findOrEmpty($post['file_ids'][0])->toArray();
//            if ($file['cid'] == $post['cid'] and $post['cid'] != 0) {
//                static::$error = '文件已在当前目录,无需移动';
//                return false;
//            }

            $model->whereIn('id', $post['file_ids'])
                ->update(['cid' => $post['cid']]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 删除文件
     * @param $post
     * @return bool
     * @author: 张无忌
     */
    public static function del($post)
    {
        try {
            $model = new File();
            $model->whereIn('id', $post['file_ids'])
                ->update(['del' => 1]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /** ======================== 华丽的分割线, 下面是文件分类相关 ========================**/

    /**
     * NOTE: 获取分类列表
     * @param $type (类型: 10=图片, 20=视频, 30=文件)
     * @return array
     * @author: 张无忌
     */
    public static function getCate($get)
    {
        try {
            $lists = FileCate::where([
                ['del', '=', 0],
                ['type', '=', $get['type']],
                ['shop_id', '=', $get['shop_id']],
            ])->order('id', 'asc')->order('sort', 'desc')->select();
            $tree = self::cateListToTree($lists, 0);

            $all = [
                'id'       => 0,
                'field'    => 'all',
                'title'    => '全部',
                'children' => [],
            ];

            array_unshift($tree, $all);
            return $tree;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * NOTE: 分类列表转树形结构
     * @author: 张无忌
     * @param $lists
     * @param int $pid
     * @return array
     */
    public static function cateListToTree($lists, $pid = 0)
    {
        $tree = [];
        foreach ($lists as $k => $v) {
            if ($v['pid'] == $pid) {
                $temp['id'] = $v['id'];
                $temp['field'] = 'id';
                $temp['title'] = $v['name'];
                $temp['children'] = self::cateListToTree($lists, $v['id']);
                $temp['spread'] = true;
                $tree[] = $temp;
            }
        }
        return $tree;
    }

    /**
     * NOTE: 分类详细
     * @author: 张无忌
     * @param $id
     * @return array
     */
    public static function getCateById($id)
    {
        $model = new FileCate();
        return $model->field(true)
            ->findOrEmpty($id)->toArray();
    }

    /**
     * NOTE: 新增分类
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function addCate($post)
    {
        try {
            if($post['pid'] == 0) {
                $level = 1;
            }else{
                $parent = FileCate::find($post['pid']);
                $level = $parent['level'] + 1;
            }
            if($post['sort'] < 0) {
                throw new \think\Exception('排序值不能为负数');
            }
            FileCate::create([
                'name' => $post['name'],
                'pid'  => $post['pid'],
                'sort' => $post['sort'],
                'shop_id' => $post['shop_id'],
                'level' => $level,
                'type' => $post['type']
            ]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }


    /**
     * NOTE: 编辑分类
     * @author: 张无忌
     * @param $post
     * @return bool
     */
    public static function editCate($post)
    {
        try {
            self::checkEdit($post);

            FileCate::update([
                'name' => $post['name'],
                'pid'  => $post['cid'],
                'sort' => $post['sort'],
                'update_time' => time()
            ], ['id'=>$post['id']]);

            self::updateLevel($post['id']);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 删除分类
     * @author: 张无忌
     * @param $id
     * @return bool
     */
    public static function delCate($id)
    {
        try {
            self::checkDel($id);

            FileCate::update([
                'del' => 1,
                'update_time' => time()
            ], ['id'=>$id]);

            return true;
        } catch (Exception $e) {
            static::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * NOTE: 下拉框树状结构
     * @param $type
     * @return array
     * @author: 张无忌
     */
    public static function categoryToSelect($get)
    {
        try {
            $model = new FileCate();
            $lists = $model->field(true)
                ->where('del', '=', 0)
                ->where('type', '=', $get['type'])
                ->where('shop_id', '=', $get['shop_id'])
                ->order('id', 'asc')
                ->select();

            $tree = [];
            foreach ($lists as $val) {

                if ($val['pid'] != 0) {
                    continue;
                }

                $tree[$val['id']] = "|----" . $val['name'];
                foreach ($lists as $val2) {
                    if ($val2['pid'] == $val['id']) {
                        $tree[$val2['id']] = "|--------" . $val2['name'];
                    }
                }

            }

            return $tree;
        } catch (Exception $e) {
            return [];
        }
    }

    public static function categoryToSelectThree($get)
    {
        try {
            $model = new FileCate();
            $lists = $model->field(true)
                ->where('del', '=', 0)
                ->where('type', '=', $get['type'])
                ->where('shop_id', '=', $get['shop_id'])
                ->order('id', 'asc')
                ->select();

            $tree = [];
            foreach ($lists as $val) {

                if ($val['pid'] != 0) {
                    continue;
                }

                $tree[$val['id']] = "|----" . $val['name'];
                foreach ($lists as $val2) {
                    if ($val2['pid'] == $val['id']) {
                        $tree[$val2['id']] = "|--------" . $val2['name'];
                        foreach($lists as $val3) {
                            if($val3['pid'] == $val2['id']) {
                                $tree[$val3['id']] = "|------------" . $val3['name'];
                            }
                        }
                    }
                }

            }

            return $tree;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 编辑验证
     */
    public static function checkEdit($post)
    {
        if(empty(trim($post['name']))) {
            throw new \think\Exception('分类名称不能为空');
        }

        if($post['id'] == $post['cid']) {
            throw new \think\Exception('上级不能是自己');
        }
        
        if($post['sort'] < 0) {
            throw new \think\Exception('排序值不能为负数');
        }

        // 获取后代分类
        $lists = FileCate::where(['del'=>0 ])->select();
        $lists = !empty($lists) ? $lists->toArray() : [];
        $childs = self::getChildCid($lists, $post['id'], true);
        if(in_array($post['cid'], $childs)) {
            throw new \think\Exception('上级不能自己的后代分类');
        }

        //层次结构不能超过三层
        $level = self::calcLevel($post['id']);
        $parent = FileCate::find($post['cid']);
        if($level + $parent['level'] > 3) {
            throw new \think\Exception('分类不允许超过三级');
        }
    }

    /**
     * 计算当前分类下共有多少层分类
     * @param $lists
     * @param $pid
     * @return int
     */
    public static function calcLevel($id)
    {
        $level = 1;
        $two_ids = FileCate::where(['pid' => $id, 'del' => 0])->column('id');
        if ($two_ids) {
            $level = 2;
            $three_ids = FileCate::where([
                ['del', '=', 0],
                ['pid', 'in', $two_ids]
            ])->column('id');
            if ($three_ids) $level = 3;
        }
        return $level;
    }

    /**
     * 更新自己及后代分类的level
     */
    public static function updateLevel($id)
    {
        $me = FileCate::find($id);
        if($me['pid'] == 0) { // 上级为顶级分类
            FileCate::update([
                'id' => $id,
                'level' => 1,
                'update_time' => time()
            ]);
            $two_ids = FileCate::where([
                'pid' => $id,
                'del' => 0
            ])->column('id');
            if($two_ids) {
                FileCate::where('id', 'in', $two_ids)->update([
                    'level' => 2,
                    'update_time' => time()
                ]);
                $three_ids = FileCate::where([
                    ['pid', 'in', $two_ids],
                    ['del', '=', 0]
                ])->column('id');
                if($three_ids) {
                    FileCate::where('id', 'in', $three_ids)->update([
                        'level' => 3,
                        'update_time' => time()
                    ]);
                }
            }
        }else{
            $parent = FileCate::find($me['pid']);
            if($parent['level'] == 1) {
                FileCate::update([
                    'id' => $id,
                    'level' => 2,
                    'update_time' => time()
                ]);
                $three_ids = FileCate::where([
                    'pid' => $id,
                    'del' => 0
                ])->column('id');
                if($three_ids) {
                    FileCate::where('id', 'in', $three_ids)->update([
                        'level' => 3,
                        'update_time' => time()
                    ]);
                }
            }else if($parent['level'] == 2){
                FileCate::update([
                    'id' => $id,
                    'level' => 3,
                    'update_time' => time()
                ]);
            }

        }
    }

    /**
     * 删除验证
     */
    public static function checkDel($id)
    {
        $file = File::where([
            'cid' => $id,
            'del' => 0
        ])->findOrEmpty();
        if(!$file->isEmpty()) {
            throw new \think\Exception('有文件正在使用当前分类，不允许删除');
        }
        $son = FileCate::where([
            'del' => 0,
            'pid' => $id
        ])->findOrEmpty();
        if(!$son->isEmpty()){
            throw new \think\Exception('分类下还有子分类，不允许删除');
        }
    }
}