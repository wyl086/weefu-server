<?php


namespace app\admin\logic\community;


use app\common\basics\Logic;
use app\common\model\community\CommunityArticle;
use app\common\model\community\CommunityTopic;


/**
 * 种草社区话题逻辑
 * Class CommunityTopicLogic
 * @package app\admin\logic\content
 */
class CommunityTopicLogic extends Logic
{

    /**
     * @notes 获取话题
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

        if (!empty($get['cid'])) {
            $where[] = ['cid', '=', $get['cid']];
        }

        $model = new CommunityTopic();
        $lists = $model->with(['cate'])
            ->where($where)
            ->order(['sort' => 'asc', 'id' => 'desc'])
            ->paginate([
                'page' => $get['page'],
                'list_rows' => $get['limit'],
                'var_page' => 'page'
            ])
            ->toArray();

        foreach ($lists['data'] as &$item) {
            $item['cate_name'] = $item['cate']['name'] ?? '';
        }


        return ['count' => $lists['total'], 'lists' => $lists['data']];
    }




    /**
     * @notes 获取话题详情
     * @param $id
     * @return array
     * @author 段誉
     * @date 2022/4/28 10:11
     */
    public static function detail($id)
    {
        return CommunityTopic::findOrEmpty($id)->toArray();
    }


    /**
     * @notes 添加话题
     * @param $post
     * @return CommunityTopic|\think\Model
     * @author 段誉
     * @date 2022/4/28 10:23
     */
    public static function add($post)
    {
        return CommunityTopic::create([
            'name' => $post['name'],
            'image' => $post['image'],
            'cid' => $post['cid'],
            'is_show' => $post['is_show'],
            'is_recommend' => $post['is_recommend'],
            'sort' => $post['sort'] ?? 255,
            'create_time' => time()
        ]);
    }


    /**
     * @notes 编辑话题
     * @param $post
     * @return CommunityTopic
     * @author 段誉
     * @date 2022/4/28 10:24
     */
    public static function edit($post)
    {
        return CommunityTopic::update([
            'name' => $post['name'],
            'image' => $post['image'],
            'cid' => $post['cid'],
            'is_show' => $post['is_show'],
            'is_recommend' => $post['is_recommend'],
            'sort' => $post['sort'] ?? 255,
            'update_time' => time()
        ], ['id' => $post['id']]);
    }


    /**
     * @notes 删除话题
     * @param $id
     * @return bool
     * @author 段誉
     * @date 2022/5/10 15:53
     */
    public static function del($id)
    {
        try {
            $check = CommunityArticle::where(['topic_id' => $id, 'del' => 0])->findOrEmpty();
            if (!$check->isEmpty()) {
                throw new \Exception('该话题下已关联文章,不可删除');
            }

            CommunityTopic::where(['id' => $id])->update([
                'del' => 1,
                'update_time' => time()
            ]);
            return true;
        } catch (\Exception $e) {
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
    public static function setStatus($post)
    {
        CommunityTopic::update([
            $post['field'] => $post['value'],
            'update_time' => time()
        ], ['id' => $post['id']]);
    }
}