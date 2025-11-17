<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddColumnToMenuDecorate extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // create the table
        $table  =  $this->table('menu_decorate');
        $table->addColumn('appid', 'string',array('default'=>'','comment'=>'跳转小程序的appid'))
            ->changeColumn('link_type', 'integer',array('limit'  =>  1,'default'=>null,'comment'=>'链接类型：1-商场模块；2-自定义链接；3-跳转小程序'))
            ->update();
    }
}
