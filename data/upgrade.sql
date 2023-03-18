ALTER TABLE `muucmf_capital_flow` CHANGE `channel` `channel` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '来源/去向balance：余额；wechat:微信';
ALTER TABLE `muucmf_wechat_config` ADD `request` VARCHAR(512) NOT NULL COMMENT '请求配置' AFTER `tmplmsg`;
ALTER TABLE `muucmf_orders` ADD  `settle` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否结算分账' AFTER `metadata`;
ALTER TABLE `muucmf_wechat_config` DROP `url`;

CREATE TABLE IF NOT EXISTS `muucmf_author_group` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(64) NOT NULL COMMENT '创作者分组标题',
  `status` TINYINT(2) NOT NULL COMMENT '状态',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='创作者分组' ROW_FORMAT=COMPACT;

INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('8553C20D-7FCB-4252-15F4-5ECFC0A56092', '创作者类型', 'D18841ED-C034-2E7A-D0B2-92D0AC647179', 4, 'admin/Author/groupList', 0, 0, '', '创作者管理', 0, 'window-restore', 'admin'),
('8BBDA16F-8A60-47CE-F4BD-0C3AC4AC7677', '添加、编辑分组', '8553C20D-7FCB-4252-15F4-5ECFC0A56092', 0, 'admin/Author/groupEdit', 0, 0, '', '', 0, '', 'admin');
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('7472E236-A4ED-6AC7-712E-3479158D5E21', '抖音小程序配置', 'A4650B98-DAD4-8194-030C-1B2AB4F35CBA', 30, 'channel/admin.DouyinMiniprogram/index', 0, 0, '', '抖音小程序', 0, '', 'admin'),
('835882C7-20C1-B7B3-1373-330D0F3E9262', '未结算订单', 'A59BE3B9-EDD9-673E-3FAC-D7AD8FC39F38', 0, 'channel/admin.DouyinMiniprogram/orders', 0, 0, '', '', 0, '', 'admin'),
('A59BE3B9-EDD9-673E-3FAC-D7AD8FC39F38', '结算分账', 'A4650B98-DAD4-8194-030C-1B2AB4F35CBA', 31, 'channel/admin.DouyinMiniprogram/settle', 0, 0, '', '抖音小程序', 0, 'jpy', 'admin');

CREATE TABLE IF NOT EXISTS `muucmf_author_follow` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `shopid` int(11) unsigned NOT NULL COMMENT '平台ID',
  `uid` int(11) unsigned NOT NULL COMMENT '谁关注',
  `author_id` int(11) unsigned NOT NULL COMMENT '关注谁',
  `status` TINYINT(2) NOT NULL COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='创造者关注表' ROW_FORMAT=COMPACT;

CREATE TABLE  IF NOT EXISTS `muucmf_douyin_mp_config` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `shopid` int(11) UNSIGNED NOT NULL COMMENT '商户ID',
  `title` varchar(20) NOT NULL COMMENT '小程序名称',
  `description` varchar(500) NOT NULL COMMENT '描述',
  `appid` varchar(40) NOT NULL COMMENT '应用ID',
  `secret` varchar(60) NOT NULL COMMENT '应用密匙',
  `weixin_merchant_uid` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '进件完成微信支付商户号',
  `alipay_merchant_uid` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '进件完成支付宝商户号',
  `token` varchar(128) NOT NULL DEFAULT '' COMMENT 'token',
  `salt` varchar(128) NOT NULL DEFAULT '' COMMENT 'salt',
  `tmplmsg` varchar(500) NOT NULL DEFAULT '' COMMENT '模板消息',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建日期',
  `update_time` int(11) UNSIGNED NOT NULL COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='抖音小程序配置表' ROW_FORMAT=COMPACT;

CREATE TABLE  IF NOT EXISTS `muucmf_douyin_mp_settle` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `shopid` int(11) UNSIGNED NOT NULL COMMENT '平台ID',
  `settle_no` varchar(64) NOT NULL COMMENT '结算单号',
  `order_no` varchar(64) NOT NULL COMMENT '关联订单号',
  `price` int(11) NOT NULL COMMENT '金额',
  `douyin_settle_no` varchar(128) NOT NULL DEFAULT '' COMMENT '平台生成分账单号',
  `status` tinyint(2) NOT NULL COMMENT '分账状态 0已发起 1已完成',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建日志',
  `update_time` int(11) UNSIGNED NOT NULL COMMENT '更新日志',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='抖音结算分账表';

UPDATE `muucmf_extend_config` SET `value` = '1:配置项\r\n2:阿里云OSS\r\n3:腾讯云COS\r\n4:阿里云短信\r\n5:腾讯云短信\r\n6:微信支付\r\n7:支付宝支付\r\n8:提现配置\r\n9:腾讯云点播' WHERE `muucmf_extend_config`.`id` = 1;

INSERT INTO `muucmf_extend_config` (`id`, `name`, `type`, `title`, `group`, `extra`, `remark`, `create_time`, `update_time`, `status`, `value`, `sort`) VALUES
(43, 'WX_PAY_CERT', 'string', '微信支付cert证书', 6, '', '', 0, 0, 1, 'file/20220804/f8b77dcab71db6c2af4f939271bbaa37.pem', 0),
(44, 'WX_PAY_KEY', 'string', '微信支付Key证书', 6, '', '', 0, 0, 1, 'file/20220804/b75d77e45e429c81a1550cd976504c60.pem', 0),
(45, 'VOD_TENCENT_KEY_SWITCH', 'radio', 'key防盗链开关', 9, '0:不启用\r\n1:启用', '腾讯云点播key防盗链开关', 0, 0, 1, '0', 0),
(46, 'VOD_TENCENT_KEY_VALUE', 'string', '防盗链 Key', 9, '', '腾讯云点播 防盗链Key值', 0, 0, 1, '', 0);

CREATE TABLE IF NOT EXISTS `muucmf_jobs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `muucmf_attachment` CHANGE `mime` `mime` CHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'mimeType';

DELETE FROM `muucmf_config` WHERE `muucmf_config`.`id` = 10147;
DELETE FROM `muucmf_config` WHERE `muucmf_config`.`id` = 10148;

ALTER TABLE `muucmf_vip` CHANGE `order_no` `order_no` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '开通时对应订单号';

ALTER TABLE `muucmf_vip_card` CHANGE `card_bg` `card_bg` VARCHAR( 255 ) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '会员卡背景';

ALTER TABLE `muucmf_orders` ADD `form_id` VARCHAR(255) NULL COMMENT 'formId 表单ID' AFTER `settle`;

CREATE TABLE IF NOT EXISTS `muucmf_baidu_mp_config` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `shopid` int(11) NOT NULL COMMENT '商户ID',
  `title` varchar(20) NOT NULL COMMENT '小程序名称',
  `description` varchar(500) NOT NULL COMMENT '描述',
  `appid` varchar(40) NOT NULL COMMENT '应用ID',
  `appkey` varchar(128) NOT NULL COMMENT 'appkey',
  `secret` varchar(60) NOT NULL COMMENT '应用密匙',
  `pay_appid` varchar(128) DEFAULT NULL COMMENT '支付服务APP ID',
  `pay_appkey` varchar(128) DEFAULT NULL COMMENT '支付服务APP KEY',
  `dealId` varchar(128) DEFAULT NULL COMMENT '支付服务 dealId',
  `rsa_public_key` text COMMENT '平台公钥',
  `rsa_private_key` text COMMENT '验签私钥',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建日期',
  `update_time` int(11) UNSIGNED NOT NULL COMMENT '更新日期',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='百度小程序配置表' ROW_FORMAT=COMPACT;

INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('0762BC67-2FAC-2C3D-94F0-9292DF07DEDE', '百度小程序配置', 'A4650B98-DAD4-8194-030C-1B2AB4F35CBA', 40, 'channel/admin.BaiduMiniprogram/index', 0, 0, '', '百度小程序', 0, '', 'admin');

ALTER TABLE `muucmf_author` ADD `professional` VARCHAR(64) NULL DEFAULT '' COMMENT '职称' AFTER `cover`;

ALTER TABLE `muucmf_wechat_config` DROP `request`;

INSERT INTO `muucmf_extend_config` (`id`, `name`, `type`, `title`, `group`, `extra`, `remark`, `create_time`, `update_time`, `status`, `value`, `sort`) VALUES
(47, 'WX_PAY_CERT_SERIAL', 'string', '微信支付商户API证书序列号', 6, '', '微信支付商户API证书序列号', 0, 0, 1, '', 0);

UPDATE `muucmf_config` SET `extra` = 'username:用户名\r\nemail:邮箱\r\nmobile:手机号\r\nqrcode:扫码（需正确配置公众号）' WHERE `muucmf_config`.`id` = 10135;

ALTER TABLE `muucmf_author` CHANGE `auth` `auth` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '功能权限 json格式';

ALTER TABLE `muucmf_keywords` CHANGE `uid` `uid` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'UID';

UPDATE `muucmf_menu` SET `url` = 'articles/admin.Articles/lists' WHERE `muucmf_menu`.`id` = '1E82B5BE-3CCF-C30D-F604-728174A3281F';

INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('0710D9F4-189C-E983-32B5-866235B03B3C', '状态管理', '25D57993-C02A-4588-0517-4836CA407079', 0, 'articles/admin.Comment/status', 0, 1, '', '', 0, '', 'articles'),
('47827768-3E92-E083-032D-4CF9A630F1C3', '状态管理', '61DAFD9B-E944-AFC4-1B4D-0130E564D4CE', 0, 'articles/admin.Articles/status', 0, 1, '', '', 0, '', 'articles'),
('627F400F-B92D-741E-5E1A-13DBAA5A8A6D', '状态管理', 'F29D6718-A1CB-861E-438C-62F9635FF98B', 0, 'articles/admin.Category/status', 0, 1, '', '', 0, '', 'articles');

ALTER TABLE `muucmf_articles_articles` ADD `author_id` INT(11) NULL DEFAULT '0' COMMENT '创作者ID' AFTER `reason`;
ALTER TABLE `muucmf_articles_articles` DROP `uid`;

ALTER TABLE `muucmf_wechat_config` ADD `auth_login` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '是否启用网页授权登录' AFTER `tmplmsg`;

INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('EDE0967F-0156-2219-F43D-6412CB252638', '状态管理', 'DA4333DF-D814-819B-D657-401FE5153AB4', 0, 'admin/Member/status', 0, 0, '', '', 0, '', 'admin');
DELETE FROM muucmf_menu WHERE `muucmf_menu`.`id` = '121A5A7C-982C-CCD9-63CA-4AA00F4D5349';
DELETE FROM muucmf_menu WHERE `muucmf_menu`.`id` = 'B88ED493-4BB4-E41A-EAEA-E501E3A685A1';

INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('E3F348B0-8DA2-C62A-062E-5C406B09D922', '状态管理', '8553C20D-7FCB-4252-15F4-5ECFC0A56092', 0, 'admin/Author/groupStatus', 0, 0, '', '', 0, '', 'admin');
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('06AEED1A-9E0E-97B9-6395-EA9959B2BE6E', '状态管理', 'A7DA37AC-E001-7C55-083F-E03A03FA5CEC', 0, 'admin/Author/status', 0, 0, '', '', 0, '', 'admin');
DELETE FROM muucmf_menu WHERE `muucmf_menu`.`id` = '826245D5-A2DC-5575-D0C3-9B36D8014D66';
DELETE FROM muucmf_menu WHERE `muucmf_menu`.`id` = '9935C318-787D-A3D9-CCFA-2EAC75CE715B';
UPDATE `muucmf_menu` SET `title` = '新增、编辑用户组' WHERE `muucmf_menu`.`id` = '4E0C013B-C00F-449F-324E-473115528F00';
DELETE FROM muucmf_menu WHERE `muucmf_menu`.`id` = '22AADF5F-AD46-2125-5833-46AE0F01D749';
DELETE FROM muucmf_menu WHERE `muucmf_menu`.`id` = '24B6E16C-401D-8CCA-C8E1-DCD118AAC005';
DELETE FROM muucmf_menu WHERE `muucmf_menu`.`id` = '76DEEC60-2249-BF97-1A6E-FA314258928F';
UPDATE `muucmf_menu` SET `title` = '状态管理', `url` = 'admin/Auth/changeStatus', `tip` = '用户组状态管理' WHERE `muucmf_menu`.`id` = '68121540-2C69-EAC2-F2EF-B7ADBBE74C09';
DELETE FROM muucmf_menu WHERE `muucmf_menu`.`id` = 'BB7A70BD-6DBB-45EB-F4A8-F7A671F62121';
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('7C934436-0EFC-A814-C5FB-521A764E75BA', '清空日志', 'A53BEFBB-17F7-56CD-ADF9-3D6754061E70', 0, 'admin/Score/clear', 0, 0, '', '', 0, '', 'admin');
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('44C36AB5-BF84-8C51-B097-FE7730A22BC3', '公告状态管理', 'DAF83BB8-F5C2-0CBB-AD8B-FEBB44D12FA3', 0, 'admin/Announce/status', 0, 0, '', '', 0, '', 'admin');
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('72483346-528E-3DFD-C1F6-5054030E78C7', '关键字状态管理', '1E10322E-B5A4-6CFE-6F33-F629B1D72A6F', 0, 'admin/Keywords/status', 0, 0, '', '', 0, '', 'admin');
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('1AF87B7B-BCC4-4493-39EE-F726D6A8DB0E', '状态管理', '4B6E56C7-715A-DEBB-F335-9B290240F781', 0, 'admin/Message/typeStatus', 0, 0, '', '', 0, '', 'admin');
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('83FB02F1-F7B5-E5B9-2D40-DED038ABE5EE', '消息状态管理', 'D15A9C87-9B85-DE05-ADCF-EAAD05BD94FD', 0, 'admin/Message/messageStatus', 0, 0, '', '', 0, '', 'admin');
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('E8197AF4-11D1-F0B1-0153-AD74521C3283', '状态管理', 'B4CCC48C-113F-4A31-9378-F9F77EEA9F4B', 0, 'admin/Feedback/status', 0, 0, '', '', 0, '', 'admin');
INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('759FD998-08F4-A420-337C-55C0D5C31B0A', '状态管理', '1B23C61A-3B9E-07DC-6FC8-DA8A2F7A80D0', 0, 'admin/History/status', 0, 0, '', '', 0, '', 'admin');

INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('D3E5BCC8-BAED-D970-9E40-6EB17CAFF94A', '用户详情', 'DA4333DF-D814-819B-D657-401FE5153AB4', 0, 'admin/Member/detail', 0, 0, '', '', 0, '', 'admin');

ALTER TABLE `muucmf_field_setting` DROP `field`;
ALTER TABLE `muucmf_field_setting` ADD `field_alias` VARCHAR(32) NOT NULL COMMENT '字段描述' AFTER `field_name`;
ALTER TABLE `muucmf_field_setting` DROP `child_form_type`;
ALTER TABLE `muucmf_action_log` CHANGE `remark` `remark` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '日志备注';

ALTER TABLE `muucmf_attachment` CHANGE `attachment` `attachment` VARCHAR(160);
ALTER TABLE `muucmf_action` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`; 
ALTER TABLE `muucmf_action_limit` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`; 
ALTER TABLE `muucmf_action_log` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`; 
ALTER TABLE `muucmf_address` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_announce` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_attachment` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_author` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_author_group` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_author_follow` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_auth_group` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_auth_group_access` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_auth_rule` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_baidu_mp_config` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_capital_flow` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_channel` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_config` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_count_active` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_crontab` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_crontab_log` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_district` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_douyin_mp_config` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_douyin_mp_settle` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_evaluate` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_extend_config` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_favorites` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_feedback` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_field` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_field_group` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_field_setting` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_follow` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_history` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_jobs` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_keywords` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_member` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_member_sync` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_member_wallet` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_menu` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_message` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_message_content` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_message_type` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_module` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_orders` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_qrcode_login` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_score_log` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_score_type` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_seo_rule` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_support` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_tominiprogram` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_user_nav` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_user_token` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_verify` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_vip` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_vip_card` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_wechat_auto_reply` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_wechat_config` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_wechat_mp_config` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
ALTER TABLE `muucmf_withdraw` CONVERT TO CHARACTER SET `utf8mb4` COLLATE `utf8mb4_general_ci`;
