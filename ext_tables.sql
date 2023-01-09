CREATE TABLE sys_file_metadata
(
	poster int(11) DEFAULT '0' NOT NULL,
	tracks int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE sys_file_reference
(
	track_language int(11)     DEFAULT '0' NOT NULL,
	track_type     varchar(30) DEFAULT ''  NOT NULL,
	'loop' TINYINT(4) DEFAULT '0' NOT NULL,
	muted TINYINT(4) DEFAULT '0' NOT NULL,
	show_related_videos TINYINT(4) DEFAULT '0' NOT NULL
);

CREATE TABLE sys_file_metadata
(
	aspect_ratio VARCHAR(10) DEFAULT '' NOT NULL
);
