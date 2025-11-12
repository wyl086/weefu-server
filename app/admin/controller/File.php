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


use app\admin\logic\FileLogic;
use app\common\basics\AdminBase;
use app\common\server\JsonServer;
use app\common\server\FileServer;

class File extends AdminBase
{
    /**
     * NOTE: 图片列表
     * @author: 张无忌
     */
    public function lists()
    {
        if ($this->request->isAjax()) {
            $get = $this->request->get();
            $lists = FileLogic::getFile($get);
            return JsonServer::success('获取成功', $lists);
        }
        
        $get = $this->request->get();
        $get['type'] = $get['type'] ?? 10; // 10 图片
        $get['shop_id'] = $get['shop_id'] ?? 0; // 0 平台

        return view('', [
            'type'     => $get['type'],
            'category' => json_encode(FileLogic::getCate($get))
        ]);
    }

    /**
     * NOTE: 移动文件
     * @author: 张无忌
     */
    public function move()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = FileLogic::move($post);
            if ($res) {
                return JsonServer::success('移动成功', FileLogic::getCate($post["type"]));
            }

            $error = FileLogic::getError() ?: '移动失败';
            return JsonServer::error($error);
        }

        $get = $this->request->get();
        $get['type'] = $get['type'] ?? 10; // 10 图片
        $get['shop_id'] = $get['shop_id'] ?? 0; // 0 平台
        return view('', [
            'categoryTree' => FileLogic::categoryToSelectThree($get)
        ]);
    }

    /**
     * NOTE: 删除文件
     * @author: 张无忌
     */
    public function del()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = FileLogic::del($post);
            $data = [
                'shop_id' => 0,
                'type' => $post['type']
            ];
            if ($res) {
                return JsonServer::success('删除成功', FileLogic::getCate($data));
            }

            $error = FileLogic::getError() ?: '删除失败';
            return JsonServer::error($error);
        }

        return JsonServer::error("请求异常");
    }

    /**
     * 其他文件上传
     */
    public function other()
    {
        if ($this->request->isPost()) {
            $local = $this->request->get('local',0);
            $sub_dir = $this->request->get('sub_dir','');
            $local = $local == 0 ? false : true;
            $save_path = 'uploads/other';

            if ($local && $local !== '') {
                $save_path = 'uploads/other/'.$sub_dir;
            }

            $result = FileServer::other($save_path, $local);
            return JsonServer::success('',$result);
        }
        return JsonServer::success();
    }

    /** ======================== 华丽的分割线, 下面是文件分类相关 ========================**/

    /**
     * NOTE: 新增分类
     * @author: 张无忌
     */
    public function addCate()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = FileLogic::addCate($post);
            $data = [
                'type' => $post['type'],
                'shop_id' => 0
            ];
            if ($res) {
                return JsonServer::success('新增成功', FileLogic::getCate($data));
            }

            $error = FileLogic::getError() ?: '新增失败';
            return JsonServer::error($error);
        }
        $get = $this->request->get();
        $get['type'] = $get['type'] ?? 10; // 10 图片
        $get['shop_id'] = $get['shop_id'] ?? 0; // 0 平台
        return view('addCate', [
            'categoryTree' => FileLogic::categoryToSelect($get)
        ]);
    }

    /**
     * NOTE: 编辑分类
     * @author: 张无忌
     */
    public function editCate()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $res = FileLogic::editCate($post);
            if ($res) {
                $data = [
                    'type' => $post["type"],
                    'shop_id' => 0
                ];
                return JsonServer::success('编辑成功', FileLogic::getCate($data));
            }

            $error = FileLogic::getError() ?: '编辑失败';
            return JsonServer::error($error);
        }

        $get = $this->request->get();
        return view('editCate', [
            'detail'       => FileLogic::getCateById($get['id']),
            'categoryTree' => FileLogic::categoryToSelect($get)
        ]);
    }

    /**
     * NOTE: 删除分类
     * @author: 张无忌
     */
    public function delCate()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->post('id');
            $type = $this->request->post('type');
            $res = FileLogic::delCate($id);
            $data = [
                'shop_id' => 0,
                'type' => $type
            ];
            if ($res) {
                return JsonServer::success('删除成功', FileLogic::getCate($data));
            }

            $error = FileLogic::getError() ?: '删除失败';
            return JsonServer::error($error, FileLogic::getCate($data));
        }

        return JsonServer::error('请求异常');
    }
}