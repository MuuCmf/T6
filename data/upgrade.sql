ALTER TABLE `muucmf_orders` ADD `refund_result` TEXT NULL COMMENT '退款成功平台返回数据' AFTER `refund_no`;
ALTER TABLE `muucmf_author` DROP `charges`;
ALTER TABLE `muucmf_author` DROP `freeze`;
ALTER TABLE `muucmf_author` DROP `total`;
ALTER TABLE `muucmf_author` ADD `group_id` INT(11) UNSIGNED NOT NULL COMMENT '分组ID' AFTER `uid`;
ALTER TABLE `muucmf_attachment` CHANGE `filename` `filename` CHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '附件显示名';
ALTER TABLE `muucmf_capital_flow` CHANGE `channel` `channel` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '来源/去向balance：余额；wechat:微信';
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='创作者分组' ROW_FORMAT=COMPACT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='创造者关注表' ROW_FORMAT=COMPACT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='抖音小程序配置表' ROW_FORMAT=COMPACT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='抖音结算分账表';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `muucmf_attachment` CHANGE `mime` `mime` CHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'mimeType';

DELETE FROM `muucmf_config` WHERE `muucmf_config`.`id` = 10147;
DELETE FROM `muucmf_config` WHERE `muucmf_config`.`id` = 10148;

ALTER TABLE `muucmf_vip` CHANGE `order_no` `order_no` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '开通时对应订单号';

ALTER TABLE `muucmf_vip_card` CHANGE `card_bg` `card_bg` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '会员卡背景';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='百度小程序配置表' ROW_FORMAT=COMPACT;

INSERT INTO `muucmf_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `type`, `tip`, `group`, `is_dev`, `icon`, `module`) VALUES
('0762BC67-2FAC-2C3D-94F0-9292DF07DEDE', '百度小程序配置', 'A4650B98-DAD4-8194-030C-1B2AB4F35CBA', 40, 'channel/admin.BaiduMiniprogram/index', 0, 0, '', '百度小程序', 0, '', 'admin');