<?php


namespace app\admin\logic\community;


use app\common\basics\Logic;
use app\common\model\community\CommunityCategory;
use app\common\model\community\CommunityTopic;
use think\Exception;


/**
 * 种草社区分类逻辑
 * Class CommunityCategoryLogic
 * @package app\admin\logic\content
 */
class CommunityCategoryLogic extends Logic
{

    /**
     * @notes 获取分类
     * @param $get
     * @return array
     * @throws \think\db\exception\DbException
     * @author 段誉
     * @date 2022/4/28 10:09
     */
    public static function lists($get)
    {
        $where = [
            ['del', '=', 0]
        ];
        
        if (!empty($get['name'])) {
            $where[] = ['name', 'like', '%'.$get['name'].'%'];
        }

        $model = new CommunityCategory();
        $lists = $model->field(true)
            ->where($where)
            ->order(['sort' => 'asc', 'id' => 'desc'])
            ->paginate([
                'page' => $get['page'],
                'list_rows' => $get['limit'],
                'var_page' => 'page'
            ])
            ->toArray();

        return ['count' => $lists['total'], 'lists' => $lists['data']];
    }


    /**
     * @notes 获取分类
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/4/28 10:11
     */
    public static function getCategory()
    {
        return CommunityCategory::where(['del' => 0, 'is_show' => 1])
            ->order(['sort' => 'asc', 'id' => 'desc'])
            ->select()
            ->toArray();
    }


    /**
     * @notes 获取分类详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/4/28 10:11
     */
    public static function detail($id)
    {
        return CommunityCategory::findOrEmpty($id)->toArray();
    }


    /**
     * @notes 添加分类
     * @param $post
     * @return CommunityCategory|\think\Model
     * @author 段誉
     * @date 2022/4/28 10:23
     */
    public static function add($post)
    {
        return CommunityCategory::create([
            'name' => $post['name'],
            'is_show' => $post['is_show'],
            'sort' => $post['sort'] ?? 255,
            'create_time' => time()
        ]);
    }


    /**
     * @notes 编辑分类
     * @param $post
     * @return CommunityCategory
     * @author 段誉
     * @date 2022/4/28 10:24
     */
    public static function edit($post)
    {
        return CommunityCategory::update([
            'name' => $post['name'],
            'is_show' => $post['is_show'],
            'sort' => $post['sort'] ?? 255,
            'update_time' => time()
        ], ['id' => $post['id']]);
    }



    /**
     * @notes 删除分类
     * @param $id
     * @return bool
     * @author 段誉
     * @date 2022/4/28 15:19
     */
    public static function del($id)
    {
        try {
            $topic = CommunityTopic::where(['del' => 0, 'cid'=> $id])->findOrEmpty();

            if (!$topic->isEmpty()) {
                throw new Exception('该分类已关联话题,暂无法删除');
            }

            CommunityCategory::update([
                'id' => $id,
                'del' => 1,
                'update_time' => time()
            ]);

            return true;

        } catch (Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }



    /**
     * @notes 设置显示状态
     * @param $post
     * @author 段誉
     * @date 2022/4/28 10:50
     */
    public static function setShowStatus($post)
    {
        CommunityCategory::update([
            'is_show' => $post['is_show'],
            'update_time' => time()
        ], ['id' => $post['id']]);
    }
}