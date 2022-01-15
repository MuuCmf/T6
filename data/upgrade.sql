DROP TABLE IF EXISTS `muucmf_test`;
CREATE TABLE `muucmf_test`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '行为唯一标识',
  `title` char(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '行为说明',
  `remark` char(140) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '行为描述',
  `rule` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '行为规则',
  `log` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '日志规则',
  `type` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型',
  `status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '状态',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
  `module` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统行为表' ROW_FORMAT = Compact;

ALTER TABLE `muucmf_test`
ADD COLUMN `id` varchar(255) NULL AFTER `update_time`;