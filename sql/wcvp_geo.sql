
CREATE TABLE `wcvp_geo` (
  `plant_name_id` int,
  `locality` varchar(40),
  `establishmentmeans` varchar(40),
  `locationid` varchar(40),
  `occurrencestatus` varchar(40),
  `threatstatus` varchar(40),
  KEY `plant_name_id` (`plant_name_id`) USING BTREE,
  KEY `locationid` (`locationid`) USING BTREE
);
