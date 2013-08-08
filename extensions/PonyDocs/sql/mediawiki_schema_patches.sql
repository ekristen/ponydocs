ALTER TABLE `user_groups` CHANGE `ug_group` `ug_group` VARBINARY(64)  NOT NULL  DEFAULT '';
ALTER TABLE `categorylinks` CHANGE `cl_sortkey` `cl_sortkey` VARBINARY(255)  NOT NULL  DEFAULT '';
