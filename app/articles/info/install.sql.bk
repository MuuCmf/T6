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
  `comment` int(10) NOT NULL COMMENT '评论量',
  `favorites` int(11) unsigned NOT NULL COMMENT '收藏量',
  `source` varchar(200) NOT NULL COMMENT '来源url',
  `reason` varchar(100) NOT NULL COMMENT '审核失败原因',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章';


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
  `status` tinyint(2) NOT NULL COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='文章评论表';


-- -----------------------------
-- 表结构 `muucmf_articles_config`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '店铺ID',
  `shopid` int(10) NOT NULL,
  `config` text NOT NULL COMMENT '配置数据，json格式',
  `status` tinyint(2) NOT NULL COMMENT '店铺状态',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='店铺表配置表';

-- -----------------------------
-- 表内记录 `muucmf_articles_articles`
-- -----------------------------
INSERT INTO `muucmf_articles_articles` VALUES ('40', '0', '1', 'sfsdfdsfsdfsdfsd', '', 'sdfdsfsfsdffsdfsdsfd', '1', 'image/20211009/a0f1292d7bd24e7e18201053b36484a9.jpg', '&lt;p&gt;dfsfsd&lt;br/&gt;&lt;/p&gt;', '1', '0', '0', '0', '0', '0', '', '', '1637228756', '1637284954');
INSERT INTO `muucmf_articles_articles` VALUES ('41', '0', '0', 'dsfdsfs', '', 'dfsdfsdfsfsf', '1', 'images/20211119/2c815cbd8f5234bba4b199243002810d.jpg', '&lt;p&gt;sdfsdfsdfsfssdfdsfsdfsdfsdfsdfsdf&lt;/p&gt;', '1', '0', '0', '0', '0', '0', '', '', '1637285925', '1637286021');
INSERT INTO `muucmf_articles_articles` VALUES ('42', '0', '0', 'sdfdsfsdfs', '', 'sdfsfsfsdfsfsfsdfs', '2', 'images/20211119/ced3ced2b55613518edeb1d559b580be.jpg', '', '1', '0', '0', '0', '0', '0', '', '', '1637286623', '1638517940');
INSERT INTO `muucmf_articles_articles` VALUES ('43', '0', '0', 'dsfsddsf', '', 'sdfdsfsdfsf', '1', 'images/20220228/6204155f9cf0db36dcdc266371af0137.jpg', '&lt;p&gt;sdfsddsfsdfs&lt;/p&gt;', '1', '0', '0', '0', '0', '0', '', '', '1646028264', '1646028264');
-- -----------------------------
-- 表内记录 `muucmf_articles_category`
-- -----------------------------
INSERT INTO `muucmf_articles_category` VALUES ('1', '0', '早期创业', '', '0', '1', '1', '3', '1', '0', '0');
INSERT INTO `muucmf_articles_category` VALUES ('2', '0', '独角兽', '', '0', '1', '1', '3', '1', '0', '0');
INSERT INTO `muucmf_articles_category` VALUES ('3', '0', '投融资', '', '0', '1', '1', '4', '1', '0', '0');
INSERT INTO `muucmf_articles_category` VALUES ('4', '0', '火木动态', '', '0', '1', '1', '3', '1', '0', '0');
INSERT INTO `muucmf_articles_category` VALUES ('5', '0', '锐观察', '', '0', '1', '1', '4', '1', '0', '0');
INSERT INTO `muucmf_articles_category` VALUES ('6', '0', '技术创新', 'image/20210930/be43b1b3347858da3b0c74eade685c59.jpg', '0', '1', '1', '5', '1', '0', '1637223542');
INSERT INTO `muucmf_articles_category` VALUES ('7', '0', 'test', '', '0', '1', '1', '8', '-1', '0', '0');
INSERT INTO `muucmf_articles_category` VALUES ('8', '0', 'test2', '', '0', '1', '1', '2', '-1', '0', '0');
INSERT INTO `muucmf_articles_category` VALUES ('9', '0', '测试', '', '6', '0', '0', '0', '1', '1637223661', '1637223661');
