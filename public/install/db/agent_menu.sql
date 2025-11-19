-- ----------------------------
-- 代理管理菜单
-- 注意：执行此SQL前，请先确认当前最大的菜单ID，然后手动替换下面的pid值
-- ----------------------------

-- 一级菜单：代理（假设最大ID为450，新ID为451）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (451, 1, 0, 0, '代理', 'layui-icon-user', '', 85, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 二级菜单：代理管理（假设新ID为452）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (452, 1, 0, 451, '代理管理', '', 'Agent/lists', 50, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 权限：代理列表（假设新ID为453）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (453, 2, 0, 452, '代理列表', '', 'Agent/lists', 50, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 权限：添加代理（假设新ID为454）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (454, 2, 0, 452, '添加代理', '', 'Agent/add', 50, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 权限：编辑代理（假设新ID为455）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (455, 2, 0, 452, '编辑代理', '', 'Agent/edit', 50, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 权限：删除代理（假设新ID为456）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (456, 2, 0, 452, '删除代理', '', 'Agent/del', 50, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 权限：设置状态（假设新ID为457）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (457, 2, 0, 452, '设置状态', '', 'Agent/status', 50, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

