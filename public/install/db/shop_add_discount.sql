-- ----------------------------
-- 为 ls_shop 表添加 discount 字段（让利百分比）
-- ----------------------------

ALTER TABLE `ls_shop` 
ADD COLUMN `discount` decimal(5, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '让利百分比' AFTER `delivery_type`;

