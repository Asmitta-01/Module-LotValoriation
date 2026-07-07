ALTER TABLE `llx_lotvalorisation_lotprice`
  ADD PRIMARY KEY (`rowid`),
  ADD UNIQUE KEY `uk_lotvalorisation_lotprice` (`fk_entrepot`,`fk_product`,`batch`),
  ADD KEY `idx_lotvalorisation_lotprice_product` (`fk_product`),
  ADD KEY `idx_lotvalorisation_lotprice_entrepot` (`fk_entrepot`);

ALTER TABLE `llx_lotvalorisation_lotprice`
  MODIFY `rowid` int(11) NOT NULL AUTO_INCREMENT;
