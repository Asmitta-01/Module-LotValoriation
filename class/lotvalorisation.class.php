<?php
/* Copyright (C) 2026 GitHub Copilot
 */

/**
 * Helper methods for lot valuation.
 */
class LotValorisation
{
    public const MODE_DEFAULT = 'default';
    public const MODE_LOT = 'lot';

    /**
     * Returns the configured mode for a warehouse.
     *
     * @param \DoliDB $db Database handler
     * @param int $warehouseId Warehouse id
     * @return string
     */
    public static function getWarehouseMode($db, $warehouseId)
    {
        if (empty($warehouseId)) {
            return self::MODE_DEFAULT;
        }

        $sql = 'SELECT mode_calc FROM ' . MAIN_DB_PREFIX . 'lotvalorisation_warehouse';
        $sql .= ' WHERE fk_entrepot = ' . ((int) $warehouseId);

        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            $db->free($resql);
            if ($obj && !empty($obj->mode_calc)) {
                return ($obj->mode_calc === self::MODE_LOT ? self::MODE_LOT : self::MODE_DEFAULT);
            }
        }

        return self::MODE_DEFAULT;
    }

    /**
     * Set the warehouse mode.
     *
     * @param \DoliDB $db Database handler
     * @param int $warehouseId Warehouse id
     * @param string $mode Mode value
     * @return int
     */
    public static function setWarehouseMode($db, $warehouseId, $mode)
    {
        $mode = ($mode === self::MODE_LOT ? self::MODE_LOT : self::MODE_DEFAULT);
        if (empty($warehouseId)) {
            return -1;
        }

        $now = dol_now();
        $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . 'lotvalorisation_warehouse';
        $sql .= ' WHERE fk_entrepot = ' . ((int) $warehouseId);
        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            $db->free($resql);

            if ($obj) {
                $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'lotvalorisation_warehouse SET mode_calc = \'' . $db->escape($mode) . '\'';
                $sql .= ', tms = \'' . $db->idate($now) . '\'';
                $sql .= ' WHERE rowid = ' . ((int) $obj->rowid);
            } else {
                $sql = 'INSERT INTO ' . MAIN_DB_PREFIX . 'lotvalorisation_warehouse (fk_entrepot, mode_calc, datec, tms)';
                $sql .= ' VALUES (' . ((int) $warehouseId) . ', \'' . $db->escape($mode) . '\', \'' . $db->idate($now) . '\', \'' . $db->idate($now) . '\')';
            }

            return ($db->query($sql) ? 1 : -1);
        }

        return -1;
    }

    /**
     * Read the stored value for a product batch in a warehouse.
     *
     * @param \DoliDB $db Database handler
     * @param int $warehouseId Warehouse id
     * @param int $productId Product id
     * @param string $batch Batch number
     * @return float|null
     */
    public static function getLotPrice($db, $warehouseId, $productId, $batch)
    {
        if (empty($warehouseId) || empty($productId) || $batch === '') {
            return null;
        }

        $sql = 'SELECT price FROM ' . MAIN_DB_PREFIX . 'lotvalorisation_lotprice';
        $sql .= ' WHERE fk_entrepot = ' . ((int) $warehouseId);
        $sql .= ' AND fk_product = ' . ((int) $productId);
        $sql .= ' AND batch = \'' . $db->escape($batch) . '\'';

        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            $db->free($resql);
            if ($obj) {
                return (float) $obj->price;
            }
        }

        return null;
    }

    /**
     * Save or update the stored value for a product batch in a warehouse.
     *
     * @param \DoliDB $db Database handler
     * @param int $warehouseId Warehouse id
     * @param int $productId Product id
     * @param string $batch Batch number
     * @param float|int|string $price Price
     * @return int
     */
    public static function saveLotPrice($db, $warehouseId, $productId, $batch, $price)
    {
        if (empty($warehouseId) || empty($productId) || $batch === '') {
            return -1;
        }

        $price = price2num($price, 'MU');
        if ($price === '') {
            $price = 0;
        }

        $now = dol_now();
        $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . 'lotvalorisation_lotprice';
        $sql .= ' WHERE fk_entrepot = ' . ((int) $warehouseId);
        $sql .= ' AND fk_product = ' . ((int) $productId);
        $sql .= ' AND batch = \'' . $db->escape($batch) . '\'';
        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            $db->free($resql);

            if ($obj) {
                $sql = 'UPDATE ' . MAIN_DB_PREFIX . 'lotvalorisation_lotprice SET price = ' . ((float) $price) . ', tms = \'' . $db->idate($now) . '\'';
                $sql .= ' WHERE rowid = ' . ((int) $obj->rowid);
            } else {
                $sql = 'INSERT INTO ' . MAIN_DB_PREFIX . 'lotvalorisation_lotprice (fk_entrepot, fk_product, batch, price, datec, tms)';
                $sql .= ' VALUES (' . ((int) $warehouseId) . ', ' . ((int) $productId) . ', \'' . $db->escape($batch) . '\', ' . ((float) $price) . ', \'' . $db->idate($now) . '\', \'' . $db->idate($now) . '\')';
            }

            return ($db->query($sql) ? 1 : -1);
        }

        return -1;
    }

    /**
     * Resolve the unit price to use for a movement.
     *
     * @param \DoliDB $db Database handler
     * @param int $warehouseId Warehouse id
     * @param int $productId Product id
     * @param string $batch Batch number
     * @param int $movementType Movement type (0,1,2,3)
     * @param float|int|string $price Incoming price
     * @return array{price:float,error:int,error_message:string,mode:string}
     */
    public static function resolveMovementPrice($db, $warehouseId, $productId, $batch, $movementType, $price)
    {
        $result = array(
            'price' => (float) price2num($price, 'MU'),
            'error' => 0,
            'error_message' => '',
            'mode' => self::MODE_DEFAULT,
        );

        if (empty($warehouseId) || empty($productId) || $batch === '') {
            return $result;
        }

        $result['mode'] = self::getWarehouseMode($db, $warehouseId);

        if ($movementType == 0 || $movementType == 3) {
            if ($result['price'] > 0) {
                self::saveLotPrice($db, $warehouseId, $productId, $batch, $result['price']);
            }
            return $result;
        }

        if ($result['mode'] === self::MODE_LOT) {
            $lotPrice = self::getLotPrice($db, $warehouseId, $productId, $batch);
            if ($lotPrice === null) {
                if ($result['price'] > 0) {
                    self::saveLotPrice($db, $warehouseId, $productId, $batch, $result['price']);
                } else {
                    $result['error'] = -1;
                    $result['error_message'] = 'Lot price not found';
                    return $result;
                }
            } else {
                $result['price'] = (float) $lotPrice;
            }
        }

        return $result;
    }

    /**
     * Return all warehouse modes for the admin page.
     *
     * @param \DoliDB $db Database handler
     * @return array<int,array{rowid:int,ref:string,lieu:string,mode:string}>
     */
    public static function getWarehouseModes($db)
    {
        $list = array();
        $sql = 'SELECT e.rowid, e.ref, e.lieu, COALESCE(w.mode_calc, \'' . self::MODE_DEFAULT . '\') as mode_calc';
        $sql .= ' FROM ' . MAIN_DB_PREFIX . 'entrepot as e';
        $sql .= ' LEFT JOIN ' . MAIN_DB_PREFIX . 'lotvalorisation_warehouse as w ON w.fk_entrepot = e.rowid';
        $sql .= ' WHERE e.entity IN (' . getEntity('stock') . ')';
        $sql .= ' ORDER BY e.ref';

        $resql = $db->query($sql);
        if ($resql) {
            while ($obj = $db->fetch_object($resql)) {
                $list[] = array(
                    'rowid' => (int) $obj->rowid,
                    'ref' => (string) $obj->ref,
                    'lieu' => (string) $obj->lieu,
                    'mode' => ($obj->mode_calc === self::MODE_LOT ? self::MODE_LOT : self::MODE_DEFAULT),
                );
            }
            $db->free($resql);
        }

        return $list;
    }
}
