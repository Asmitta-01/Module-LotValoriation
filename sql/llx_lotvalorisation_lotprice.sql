CREATE TABLE IF NOT EXISTS `llx_lotvalorisation_lotprice` (
  `rowid` int(11) NOT NULL,
  `fk_entrepot` int(11) NOT NULL,
  `fk_product` int(11) NOT NULL,
  `batch` varchar(30) NOT NULL,
  `price` double(24,8) NOT NULL DEFAULT 0,
  `datec` datetime NOT NULL,
  `tms` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
