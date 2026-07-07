<?php
/* Copyright (C) 2026 GitHub Copilot
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 * \defgroup   lotvalorisation     Module lotvalorisation
 * \brief      Module de valorisation de sortie par lot et par entrepot
 */

require_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

/**
 * Module lotvalorisation
 */
class modLotvalorisation extends \DolibarrModules
{
    /**
     * Constructor.
     *
     * @param \DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->numero = 104510;
        $this->rights_class = 'lotvalorisation';
        $this->family = 'products';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = 'Valorisation de sortie par lot et par entrepot';
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
        $this->picto = 'stock';
        $this->config_page_url = array('setup.php@lotvalorisation');
        $this->depends = array('modProduct', 'modStock');
        $this->module_parts = array(
            'hooks' => array(
                'data' => array('mouvementstock')
            )
        );
        $this->dirs = array();
        $this->langfiles = array('lotvalorisation@lotvalorisation');
        $this->rights = array();
    }

    /**
     * Enable module.
     *
     * @param string $options Options
     * @return int
     */
    public function init($options = '')
    {
        $sql = array();
        $result = $this->load_tables();
        if ($result < 0) {
            return $result;
        }

        return $this->_init($sql, $options);
    }

    /**
     * Disable module.
     *
     * @param string $options Options
     * @return int
     */
    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }

    /**
     * Load SQL tables.
     *
     * @return int
     */
    public function load_tables()
    {
        return $this->_load_tables('/lotvalorisation/sql/');
    }
}
