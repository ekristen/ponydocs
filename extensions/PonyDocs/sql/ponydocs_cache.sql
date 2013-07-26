CREATE TABLE `ponydocs_cache` (
	`cachekey` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`expires` INT NOT NULL DEFAULT 0,
	`data` longtext COLLATE utf8_unicode_ci,
	PRIMARY KEY  (`cachekey`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;