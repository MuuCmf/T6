ALTER TABLE `muucmf_articles_articles` ADD `author_id` INT(11) NULL DEFAULT '0' COMMENT '创作者ID' AFTER `reason`;
ALTER TABLE `muucmf_articles_articles` DROP `uid`;