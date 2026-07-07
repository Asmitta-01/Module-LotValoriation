CREATE TABLE IF NOT EXISTS `llx_lotvalorisation_warehouse` (
  `rowid` int(11) NOT NULL,
  `fk_entrepot` int(11) NOT NULL,
  `mode_calc` varchar(16) NOT NULL DEFAULT 'default',
  `datec` datetime NOT NULL,
  `tms` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
