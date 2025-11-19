-- ----------------------------
-- Table structure for ls_agent
-- ----------------------------
DROP TABLE IF EXISTS `ls_agent`;
CREATE TABLE `ls_agent`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `pid` int(11) NOT NULL DEFAULT 1 COMMENT '推荐人ID（agent表的id）',
  `invite_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '邀请码',
  `source` tinyint(1) NOT NULL DEFAULT 1 COMMENT '来源：1-商户；2-用户',
  `source_id` int(11) NOT NULL DEFAULT 0 COMMENT '来源ID（商户ID或用户ID）',
  `mobile` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `province_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '省ID',
  `city_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '市ID',
  `district_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '区ID',
  `is_city_agent` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否市级代理：0-否；1-是',
  `is_district_agent` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否区级代理：0-否；1-是',
  `is_service` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否服务商：0-否；1-是',
  `is_promoter` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否推广员：0-否；1-是',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0-禁用；1-启用',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  `del` tinyint(10) NOT NULL DEFAULT 0 COMMENT '0为非删除状态，非0位删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_invite_code`(`invite_code`) USING BTREE,
  INDEX `idx_source_source_id`(`source`, `source_id`) USING BTREE,
  INDEX `idx_mobile`(`mobile`) USING BTREE,
  INDEX `idx_province_city_district`(`province_id`, `city_id`, `district_id`) USING BTREE,
  INDEX `idx_pid`(`pid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '代理表';
