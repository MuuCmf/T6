ALTER TABLE `muucmf_orders` ADD `refund_result` TEXT NULL COMMENT '退款成功平台返回数据' AFTER `refund_no`;

ALTER TABLE `muucmf_author` DROP `charges`;
ALTER TABLE `muucmf_author` DROP `freeze`;
ALTER TABLE `muucmf_author` DROP `total`;
ALTER TABLE `muucmf_author` ADD `group_id` INT(11) UNSIGNED NOT NULL COMMENT '分组ID' AFTER `uid`;

CREATE TABLE IF NOT EXISTS `muucmf_author_group` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(64) NOT NULL COMMENT '创作者分组标题',
  `status` TINYINT(2) NOT NULL COMMENT '状态',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='创作者分组' ROW_FORMAT=COMPACT;

INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('8553C20D-7FCB-4252-15F4-5ECFC0A56092', '创作者类型', 'D18841ED-C034-2E7A-D0B2-92D0AC647179', 4, 'admin/Author/groupList', 0, 0, '', '创作者管理', 0, 'window-restore', 'admin'),
('8BBDA16F-8A60-47CE-F4BD-0C3AC4AC7677', '添加、编辑分组', '8553C20D-7FCB-4252-15F4-5ECFC0A56092', 0, 'admin/Author/groupEdit', 0, 0, '', '', 0, '', 'admin');

CREATE TABLE IF NOT EXISTS `muucmf_author_follow` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `shopid` int(11) unsigned NOT NULL COMMENT '平台ID',
  `uid` int(11) unsigned NOT NULL COMMENT '谁关注',
  `author_id` int(11) unsigned NOT NULL COMMENT '关注谁',
  `status` TINYINT(2) NOT NULL COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='创造者关注表' ROW_FORMAT=COMPACT;

ALTER TABLE `muucmf_attachment` CHANGE `filename` `filename` CHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '附件显示名';

ALTER TABLE `muucmf_capital_flow` CHANGE `channel` `channel` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '来源/去向balance：余额；wechat:微信';