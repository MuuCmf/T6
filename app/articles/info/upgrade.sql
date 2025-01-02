ALTER TABLE `muucmf_articles_articles` ADD `author_id` INT(11) NULL DEFAULT '0' COMMENT '创作者ID' AFTER `reason`;
ALTER TABLE `muucmf_articles_articles` DROP `uid`;

ALTER TABLE `muucmf_articles_articles` CHANGE `content` `content` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '内容';