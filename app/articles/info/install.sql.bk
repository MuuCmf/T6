-- -----------------------------
-- 表结构 `muucmf_articles_articles`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `shopid` int(11) unsigned NOT NULL COMMENT '店铺ID',
  `uid` int(11) unsigned NOT NULL COMMENT '用户ID',
  `title` varchar(50) NOT NULL COMMENT '标题',
  `keywords` varchar(255) NOT NULL COMMENT '关键字，多个用,分割',
  `description` varchar(200) NOT NULL COMMENT '描述',
  `category_id` int(11) unsigned NOT NULL COMMENT '分类ID',
  `cover` varchar(255) NOT NULL COMMENT '封面',
  `content` text NOT NULL COMMENT '内容',
  `status` tinyint(2) NOT NULL COMMENT '状态',
  `sort` int(5) NOT NULL COMMENT '排序',
  `position` int(4) NOT NULL COMMENT '定位，展示位',
  `view` int(10) NOT NULL COMMENT '阅读量',
  `f_view` int(10) NOT NULL COMMENT '自定义浏览量',
  `comment` int(10) NOT NULL COMMENT '评论量',
  `favorites` int(11) unsigned NOT NULL COMMENT '收藏量',
  `f_favorites` int(10) NOT NULL COMMENT '自定义收藏量',
  `support` int(10) NOT NULL COMMENT '点赞量',
  `f_support` int(10) NOT NULL COMMENT '自定义点赞量',
  `source` varchar(200) NOT NULL COMMENT '来源url',
  `reason` varchar(100) NOT NULL COMMENT '审核失败原因',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章';


-- -----------------------------
-- 表结构 `muucmf_articles_category`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `shopid` int(11) unsigned NOT NULL COMMENT '店铺ID',
  `title` varchar(32) NOT NULL COMMENT '标题',
  `cover` varchar(256) NOT NULL COMMENT '图标',
  `pid` int(11) unsigned NOT NULL COMMENT '上级分类ID',
  `can_post` tinyint(4) NOT NULL COMMENT '前台可投稿',
  `need_audit` tinyint(4) NOT NULL COMMENT '前台投稿是否需要审核',
  `sort` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) NOT NULL COMMENT '状态',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章分类';


-- -----------------------------
-- 表结构 `muucmf_articles_comment`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `shopid` int(11) NOT NULL COMMENT '平台ID',
  `uid` int(11) unsigned NOT NULL COMMENT '用户ID',
  `pid` int(11) unsigned NOT NULL COMMENT '上级评论ID',
  `article_id` int(11) unsigned NOT NULL COMMENT '文章ID',
  `content` text NOT NULL COMMENT '评论内容',
  `support` int(11) unsigned NOT NULL COMMENT '点赞数量',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  `reason` varchar(255) NOT NULL COMMENT '审核失败原因',
  `status` tinyint(2) NOT NULL COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='文章评论表';


-- -----------------------------
-- 表结构 `muucmf_articles_config`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '店铺ID',
  `shopid` int(10) NOT NULL,
  `comment` text NOT NULL COMMENT '评论配置数据，json格式',
  `status` tinyint(2) NOT NULL COMMENT '店铺状态',
  `close_desc` varchar(255) NOT NULL COMMENT '应用关闭时的描述',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='店铺表配置表';

