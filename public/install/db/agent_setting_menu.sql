-- ----------------------------
-- 代理设置菜单
-- 注意：执行此SQL前，请先确认代理菜单的ID（通常是451）
-- ----------------------------

-- 二级菜单：设置（假设代理菜单ID为451，新ID为458）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (458, 1, 0, 451, '设置', '', 'Agent/setting', 60, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 权限：查看设置（假设新ID为459）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (459, 2, 0, 458, '查看设置', '', 'Agent/setting', 50, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

-- 权限：保存设置（假设新ID为460）
INSERT INTO `ls_dev_auth` (`id`, `type`, `system`, `pid`, `name`, `icon`, `uri`, `sort`, `disable`, `create_time`, `update_time`, `del`) 
VALUES (460, 2, 0, 458, '保存设置', '', 'Agent/setting', 50, 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0);

