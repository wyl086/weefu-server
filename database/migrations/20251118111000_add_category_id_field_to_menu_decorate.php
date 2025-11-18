<?php

use think\migration\Migrator;

class AddCategoryIdFieldToMenuDecorate extends Migrator
{
    public function change()
    {
        $table = $this->table('menu_decorate');
        $table->addColumn('category_id', 'integer', ['default' => 0, 'comment' => '平台商品分类ID'])
            ->changeColumn('link_type', 'integer', ['limit' => 1, 'comment' => '链接类型：1-商场模块；2-自定义链接；3-跳转小程序；4-平台商品分类'])
            ->update();
    }
}
