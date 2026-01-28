-- 升级扩展配置表脚本
-- 生成日期：2026-01-28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- 检查旧表是否存在，如果不存在则创建
CREATE TABLE IF NOT EXISTS `muucmf_extend_config_old` (
  `id` int(11) UNSIGNED NOT NULL COMMENT '配置ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` char(32) NOT NULL COMMENT '配置类型',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '配置说明',
  `group` varchar(32) NOT NULL COMMENT '配置分组',
  `extra` varchar(255) NOT NULL DEFAULT '' COMMENT '配置值',
  `remark` varchar(500) NOT NULL COMMENT '配置说明',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `value` mediumtext NOT NULL COMMENT '配置值',
  `sort` smallint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- 添加索引
ALTER TABLE `muucmf_extend_config_old`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `uk_name` (`name`) USING BTREE,
  ADD KEY `type` (`type`) USING BTREE,
  ADD KEY `group` (`group`) USING BTREE;

-- 从新表复制数据到旧表（如果旧表为空）
INSERT IGNORE INTO `muucmf_extend_config_old` (`id`, `name`, `type`, `title`, `group`, `extra`, `remark`, `create_time`, `update_time`, `status`, `value`, `sort`)
SELECT `id`, `name`, `type`, `title`, `group`, `extra`, `remark`, `create_time`, `update_time`, `status`, `value`, `sort`
FROM `muucmf_extend_config`;

-- 更新group字段内容（从新表复制）
UPDATE `muucmf_extend_config_old` AS old
JOIN `muucmf_extend_config` AS new ON old.id = new.id
SET old.`group` = new.`group`;

-- 更新id为1的value字段内容
UPDATE `muucmf_extend_config_old`
SET `value` = 'public:公共配置项\naliyun_oss:阿里云OSS\ntencent_cos:腾讯云COS\naliyun_sms:阿里云短信\ntencent_sms:腾讯云短信\nweixinpay:微信支付\nalipay:支付宝支付\ntencent_vod:腾讯云点播\nstore:存储配置\nsms:短信配置\nvod:点播配置\npay:支付配置\nwithdraw:提现配置'
WHERE id = 1;

-- 为旧表设置自增ID
ALTER TABLE `muucmf_extend_config_old`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID', AUTO_INCREMENT=59;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
