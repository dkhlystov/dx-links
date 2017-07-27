create table if not exists `link_domain`
(
	`id` int(10) not null auto_increment,
	`scheme` varchar(10) not null,
	`host` varchar(100) not null,
	`user_id` int(10) not null,
	primary key (`id`),
	unique `domain` (`scheme`, `host`)
) engine InnoDB;

create table if not exists `link_domain_user`
(
	`id` int(10) not null auto_increment,
	`domain_id` int(10) not null,
	`user_id` int(10) default null,
	`hash` varchar(32) not null,
	`verified` tinyint(1) default 0,
	primary key (`id`),
	foreign key (`domain_id`) references `link_domain` (`id`) on delete cascade on update cascade
) engine InnoDB;

create table if not exists `link_url`
(
	`id` int(10) not null auto_increment,
	`domain_id` int(10) not null,
	`url` varchar(500) not null,
	`depth` int(10) default null,
	`status` int(10) default null,
	`lastModified` datetime default null,
	`expires` datetime default null,
	`redirect` varchar(500) default null,
	`title` varchar(100) default null,
	`description` varchar(200) default null,
	`keywords` varchar(200) default null,
	`size` int(10) default null,
	`loadTime` int(10) default null,
	primary key (`id`),
	foreign key (`domain_id`) references `link_domain` (`id`) on delete cascade on update cascade,
	unique `url` (`domain_id`, `url`)
) engine InnoDB;

create table if not exists `link_url_rel`
(
	`id` int(10) not null auto_increment,
	`src_id` int(10) not null,
	`dest_id` int(10) not null,
	primary key (`id`),
	foreign key (`src_id`) references `link_url` (`id`) on delete cascade on update cascade,
	foreign key (`dest_id`) references `link_url` (`id`) on delete cascade on update cascade
) engine InnoDB;

create table if not exists `link_url_confirmity`
(
	`id` int(10) not null auto_increment,
	`src_id` int(10) not null,
	`domain_id` int(10) not null,
	`dest_id` int(10) not null,
	primary key (`id`),
	foreign key (`src_id`) references `link_url` (`id`) on delete cascade on update cascade,
	foreign key (`domain_id`) references `link_domain` (`id`) on delete cascade on update cascade,
	foreign key (`dest_id`) references `link_url` (`id`) on delete cascade on update cascade,
	unique `url` (`src_id`, `domain_id`)
) engine InnoDB;
