<?php

require __DIR__ . '/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
dol_include_once('/lotvalorisation/class/lotvalorisation.class.php');

global $db;

$langs->load('lotvalorisation@lotvalorisation');

if (!$user->admin) {
    accessforbidden();
}

$search_warehouse = GETPOSTINT('search_warehouse');
$search_product = GETPOSTINT('search_product');
$search_batch = GETPOST('search_batch', 'alphanohtml');

$form = new \Form($db);
$formproduct = new \FormProduct($db);

$sql = 'SELECT lp.rowid, lp.fk_entrepot, lp.fk_product, lp.batch, lp.price, lp.tms,';
$sql .= ' e.ref as warehouse_ref, p.ref as product_ref, p.label as product_label';
$sql .= ' FROM ' . MAIN_DB_PREFIX . 'lotvalorisation_lotprice as lp';
$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'entrepot as e ON e.rowid = lp.fk_entrepot';
$sql .= ' INNER JOIN ' . MAIN_DB_PREFIX . 'product as p ON p.rowid = lp.fk_product';
$sql .= ' WHERE e.entity IN (' . getEntity('stock') . ')';

if ($search_warehouse > 0) {
    $sql .= ' AND lp.fk_entrepot = ' . ((int) $search_warehouse);
}
if ($search_product > 0) {
    $sql .= ' AND lp.fk_product = ' . ((int) $search_product);
}
if ($search_batch !== '') {
    $sql .= " AND lp.batch LIKE '%" . $db->escape($search_batch) . "%'";
}

$sql .= ' ORDER BY e.ref ASC, p.ref ASC, lp.batch ASC';

$resql = $db->query($sql);

llxHeader('', $langs->trans('LotValuationPrices'));

$linkback = strpos($_SERVER['HTTP_REFERER'], DOL_URL_ROOT . '/admin/modules.php') !== false ? '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>' : '';
print load_fiche_titre($langs->trans('LotValuationPrices'), $linkback, 'stock');

print '<form method="GET" action="' . $_SERVER['PHP_SELF'] . '">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans('Warehouse') . '</td>';
print '<td>' . $langs->trans('Product') . '</td>';
print '<td>' . $langs->trans('Batch') . '</td>';
print '<td class="right">&nbsp;</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>' . $formproduct->selectWarehouses($search_warehouse, 'search_warehouse', 'warehouseopen,warehouseinternal', 1) . '</td>';
print '<td>' . $form->select_produits($search_product, 'search_product', '', 0, 0, -1, 0, '', 0, array(), 0, 0, 0, 'minwidth200') . '</td>';
print '<td><input type="text" class="flat" name="search_batch" value="' . dol_escape_htmltag($search_batch) . '" size="14"></td>';
print '<td class="right">';
print '<input type="submit" class="button" value="' . $langs->trans('Search') . '">';
print '&nbsp;';
print '<a class="button button-cancel" href="' . $_SERVER['PHP_SELF'] . '">' . $langs->trans('Reset') . '</a>';
print '</td>';
print '</tr>';
print '</table>';
print '</form>';

print '<br>';
print '<div class="div-table-responsive">';
print '<table class="liste centpercent">';
print '<tr class="liste_titre">';
print '<th>' . $langs->trans('Warehouse') . '</th>';
print '<th>' . $langs->trans('Product') . '</th>';
print '<th>' . $langs->trans('Batch') . '</th>';
print '<th class="right">' . $langs->trans('UnitPurchaseValue') . '</th>';
print '<th>' . $langs->trans('DateModificationShort') . '</th>';
print '</tr>';

if ($resql) {
    $nb = $db->num_rows($resql);
    if ($nb > 0) {
        while ($obj = $db->fetch_object($resql)) {
            print '<tr class="oddeven">';
            print '<td>' . dol_escape_htmltag($obj->warehouse_ref) . '</td>';
            print '<td>' . dol_escape_htmltag($obj->product_ref . ' - ' . $obj->product_label) . '</td>';
            print '<td>' . dol_escape_htmltag($obj->batch) . '</td>';
            print '<td class="right">' . price($obj->price) . '</td>';
            print '<td>' . dol_print_date($db->jdate($obj->tms), 'dayhour') . '</td>';
            print '</tr>';
        }
    } else {
        print '<tr class="oddeven"><td colspan="5" class="opacitymedium">' . $langs->trans('NoRecordFound') . '</td></tr>';
    }
    $db->free($resql);
} else {
    print '<tr class="oddeven"><td colspan="5" class="error">' . $db->lasterror() . '</td></tr>';
}

print '</table>';
print '</div>';

llxFooter();
