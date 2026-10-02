<?php
/* Copyright (C) 2026 GitHub Copilot
 */

dol_include_once('/lotvalorisation/class/lotvalorisation.class.php');

/**
 * Hook actions for lot valuation module.
 */
class ActionsLotvalorisation
{
    public $db;
    public $error = '';
    public $errors = array();

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Intercept stock movement creation to resolve lot valuation.
     *
     * @param array<string,mixed> $parameters Parameters passed by hook manager
     * @param object $object Current object
     * @param string $action Current action
     * @return int
     */
    public function stockMovementCreate($parameters, &$object, &$action)
    {
        global $langs;

        $warehouseId = !empty($parameters['entrepot_id']) ? (int) $parameters['entrepot_id'] : 0;
        $productId = !empty($parameters['fk_product']) ? (int) $parameters['fk_product'] : 0;
        $movementType = !empty($parameters['type']) ? (int) $parameters['type'] : 0;
        $batch = !empty($parameters['batch']) ? (string) $parameters['batch'] : '';
        $price = isset($parameters['price']) ? $parameters['price'] : 0;

        if (empty($warehouseId) || empty($productId)) {
            return 0;
        }

        $result = \LotValorisation::resolveMovementPrice($object->db, $warehouseId, $productId, $batch, $movementType, $price);
        $parameters['price'] = $result['price'];

        if (!empty($result['error'])) {
            $this->error = $langs->trans('Error');
            $this->errors = array($result['error_message']);
            return -1;
        }

        return 0;
    }
}
