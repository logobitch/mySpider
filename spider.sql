create table `spider`(
  `id` int unsigned not null auto_increment,
  `title` varchar(100) not null default '' comment '标题',
  `desc` varchar(500) not null default '' comment '描述',
  `author` varchar(100) not null default '' comment '作者',
  `editor` varchar(100) not null default '' comment '编辑',
  `created_at` timestamp not null default CURRENT_TIMESTAMP comment '创建时间',
  `updated_at` timestamp not null default CURRENT_TIMESTAMP comment '编辑时间',
  primary key (id)
);