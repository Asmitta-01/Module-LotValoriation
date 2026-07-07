ALTER TABLE `llx_lotvalorisation_warehouse`
  ADD PRIMARY KEY (`rowid`),
  ADD UNIQUE KEY `uk_lotvalorisation_warehouse` (`fk_entrepot`);

ALTER TABLE `llx_lotvalorisation_warehouse`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;
