create table `spider`(
  `spider_id` int unsigned not null auto_increment,
  `title` varchar(100) not null default '' comment '标题',
  `desc` varchar(500) not null default '' comment '描述',
  `author` varchar(100) not null default '' comment '作者',
  `editor` varchar(100) not null default '' comment '编辑',
  `content` text not null comment '文章内容',
  `created_at` timestamp not null default CURRENT_TIMESTAMP comment '创建时间',
  `updated_at` timestamp not null default CURRENT_TIMESTAMP comment '编辑时间',
  primary key (`spider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='暂存抓取的文章信息表';


create table `spidered`(
  `spidered_id` int unsigned NOT NULL AUTO_INCREMENT,
   `spidered_key` varchar(32) NOT NULL DEFAULT '' COMMENT '网站蜘蛛标识',
   `url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '抓取url',
   `content_id` int NOT NULL DEFAULT '0' COMMENT '抓取内容状态',
   `created_at` timestamp not null default CURRENT_TIMESTAMP comment '创建时间',
   `updated_at` timestamp not null default CURRENT_TIMESTAMP comment '更新时间',
   PRIMARY KEY (`spidered_id`),
   UNIQUE KEY (`spidered_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='已抓取内容标识表';